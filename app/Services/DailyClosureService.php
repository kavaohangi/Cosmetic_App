<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\Role;
use App\Enums\SaleType;
use App\Enums\StockMovementType;
use App\Models\DailyClosure;
use App\Models\DayEntity;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\TerrainReport;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Daily closure engine. Working days are every day except Sunday. Closing a day
 * first ensures the DayEntity exists, then generates a role-specific report
 * snapshot automatically.
 */
class DailyClosureService
{
    /**
     * Whether the given date is a working day (not Sunday).
     */
    public function isWorkingDay(CarbonImmutable $date): bool
    {
        return ! $date->isSunday();
    }

    public function alreadyClosed(User $user, CarbonImmutable $date): bool
    {
        return DailyClosure::query()
            ->whereHas('day', fn ($q) => $q->whereDate('date', $date->toDateString()))
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Close the day for the given user and generate the automatic report.
     */
    public function close(User $user, CarbonImmutable $date): DailyClosure
    {
        if (! $this->isWorkingDay($date)) {
            throw new RuntimeException('Le dimanche n\'est pas un jour ouvrable : aucune clôture requise.');
        }

        if ($this->alreadyClosed($user, $date)) {
            throw new RuntimeException('Vous avez déjà clôturé cette journée.');
        }

        return DB::transaction(function () use ($user, $date): DailyClosure {
            $day = DayEntity::forDate($date);

            $report = $this->buildReport($user, $date);

            $closure = DailyClosure::create([
                'day_id' => $day->id,
                'user_id' => $user->id,
                'role' => $user->role,
                'ventes_credit' => $report['ventes_credit'] ?? 0,
                'ventes_comptant' => $report['ventes_comptant'] ?? 0,
                'payload' => $report,
                'closed_at' => now(),
            ]);

            // The day is globally closed once every active role-holder has closed,
            // but we mark it closed as soon as a closure exists for traceability.
            if (! $day->is_closed) {
                $day->update(['is_closed' => true, 'closed_at' => now()]);
            }

            return $closure;
        });
    }

    /**
     * Build the role-specific automatic report payload.
     *
     * @return array<string, mixed>
     */
    public function buildReport(User $user, CarbonImmutable $date): array
    {
        return match ($user->role) {
            Role::Magasinier => $this->magasinierReport($date),
            Role::ChefMarketing => $this->salesReport($date, null),
            Role::AgentMarketeur => $this->salesReport($date, $user->id),
            Role::MarketeurTerrain => $this->terrainReport($user, $date),
            default => ['type' => 'generic', 'date' => $date->toDateString()],
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function magasinierReport(CarbonImmutable $date): array
    {
        $movements = StockMovement::query()
            ->whereDate('created_at', $date->toDateString())
            ->with('product')
            ->get();

        $byType = fn (StockMovementType $type) => $movements
            ->where('type', $type)
            ->groupBy('product_id')
            ->map(fn ($g) => [
                'product' => $g->first()->product?->name,
                'quantite' => (int) $g->sum('quantite'),
            ])
            ->values()
            ->all();

        $enRupture = Product::query()->enRupture()->get();

        $ruptureCommandes = Product::query()
            ->enRupture()
            ->whereHas('orderItems.order', fn ($q) => $q->whereIn('statut', ['en_attente', 'validee']))
            ->pluck('name')
            ->all();

        return [
            'type' => 'magasinier',
            'date' => $date->toDateString(),
            'produits_ajoutes' => $byType(StockMovementType::Entree),
            'produits_ajustes' => $byType(StockMovementType::Ajustement),
            'produits_sortis' => $byType(StockMovementType::Sortie),
            'produits_en_rupture' => $enRupture->pluck('name')->all(),
            'produits_en_rupture_commandes' => $ruptureCommandes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function salesReport(CarbonImmutable $date, ?int $agentId): array
    {
        $deliveries = Delivery::query()
            ->whereDate('date', $date->toDateString())
            ->where('statut', DeliveryStatus::Livre->value)
            ->when($agentId !== null, fn ($q) => $q->where('agent_id', $agentId))
            ->with(['lines.product', 'client'])
            ->get();

        $credit = 0.0;
        $comptant = 0.0;
        $lignes = [];

        foreach ($deliveries as $delivery) {
            $montant = $delivery->montantTotal();

            if ($delivery->type_vente === SaleType::Credit) {
                $credit += $montant;
            } else {
                $comptant += $montant;
            }

            $lignes[] = [
                'reference' => $delivery->reference,
                'client' => $delivery->client?->name,
                'type' => $delivery->type_vente->value,
                'montant' => round($montant, 2),
                'produits' => $delivery->lines->map(fn ($l) => [
                    'product' => $l->product?->name,
                    'livree' => $l->quantite,
                    'rendue' => $l->quantite_rendue,
                    'nette' => $l->quantiteNette(),
                ])->all(),
            ];
        }

        return [
            'type' => $agentId !== null ? 'agent_marketeur' : 'chef_marketing',
            'date' => $date->toDateString(),
            'ventes_credit' => round($credit, 2),
            'ventes_comptant' => round($comptant, 2),
            'nb_livraisons' => $deliveries->count(),
            'livraisons' => $lignes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function terrainReport(User $user, CarbonImmutable $date): array
    {
        $reports = TerrainReport::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $date->toDateString())
            ->with('items.product')
            ->get();

        $produits = $reports
            ->flatMap(fn (TerrainReport $r) => $r->items)
            ->groupBy('product_id')
            ->map(fn ($g) => [
                'product' => $g->first()->product?->name,
                'quantite' => (int) $g->sum('quantite'),
                'montant' => round((float) $g->sum('sous_total'), 2),
            ])
            ->values()
            ->all();

        return [
            'type' => 'marketeur_terrain',
            'date' => $date->toDateString(),
            'magasin' => $user->magasin,
            'nb_rapports' => $reports->count(),
            'produits' => $produits,
            'total' => round((float) $reports->sum(fn (TerrainReport $r) => $r->items->sum('sous_total')), 2),
        ];
    }
}
