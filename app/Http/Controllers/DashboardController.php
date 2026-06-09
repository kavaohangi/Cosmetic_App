<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Models\Order;
use App\Models\Product;
use App\Models\TerrainReport;
use App\Models\User;
use App\Services\TerrainService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private TerrainService $terrainService) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role === Role::Client) {
            return redirect()->route('portal.dashboard');
        }

        $data = match ($user->role) {
            Role::ChefMarketing => $this->chefData(),
            Role::AgentMarketeur => $this->agentData($user),
            Role::MarketeurTerrain => $this->terrainData($user),
            Role::Magasinier => $this->stockData(),
            default => $this->genericData(),
        };

        return view('dashboard', [
            'role' => $user->role,
            ...$data,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function chefData(): array
    {
        return [
            'kpis' => [
                'commandes_a_valider' => Order::query()->enAttente()->count(),
                'ca_equipe' => (float) Order::query()
                    ->whereIn('statut', [OrderStatus::Validee->value, OrderStatus::Livree->value])
                    ->sum('total'),
                'agents_actifs' => User::query()
                    ->withRole(Role::AgentMarketeur)
                    ->where('is_active', true)
                    ->count(),
            ],
            'pendingOrders' => Order::query()
                ->enAttente()
                ->with(['client', 'user'])
                ->latest('date_commande')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function agentData(User $user): array
    {
        $reports = $this->terrainService->getMyTeamReports($user->id);

        return [
            'kpis' => [
                'commandes_en_attente' => Order::query()
                    ->where('user_id', $user->id)
                    ->enAttente()
                    ->count(),
                'en_cours' => Order::query()
                    ->where('user_id', $user->id)
                    ->whereIn('statut', [OrderStatus::Validee->value, OrderStatus::EnPreparation->value])
                    ->count(),
                'livrees' => Order::query()
                    ->where('user_id', $user->id)
                    ->where('statut', OrderStatus::Livree->value)
                    ->count(),
                'refusees' => Order::query()
                    ->where('user_id', $user->id)
                    ->where('statut', OrderStatus::Annulee->value)
                    ->count(),
            ],
            'recentOrders' => Order::query()
                ->where('user_id', $user->id)
                ->with(['client'])
                ->latest('date_commande')
                ->limit(10)
                ->get(),
            'teamReports' => $reports->take(10),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function terrainData(User $user): array
    {
        $today = today();
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $todayReports = $user->terrainReports()
            ->whereDate('date', $today)
            ->get();
        $weekReports = $user->terrainReports()
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->get();
        $monthReports = $user->terrainReports()
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        $todayComplaints = $user->terrainComplaints()
            ->whereDate('date', $today)
            ->count();
        $pendingComplaints = $user->terrainComplaints()
            ->where('status', 'pending')
            ->count();

        return [
            'supervisor' => $user->supervisor,
            'colleagues' => $this->terrainService->getColleagues($user->id),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->limit(8)->get(),
            'todayReport' => $todayReports->first(),
            'kpis' => [
                'today_sales' => $todayReports->sum(fn ($r) => $r->items->sum('sous_total')),
                'week_sales' => $weekReports->sum(fn ($r) => $r->items->sum('sous_total')),
                'month_sales' => $monthReports->sum(fn ($r) => $r->items->sum('sous_total')),
                'today_complaints' => $todayComplaints,
                'pending_complaints' => $pendingComplaints,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function stockData(): array
    {
        return [
            'kpis' => [
                'total_produits' => Product::query()->count(),
                'unites_en_stock' => (int) Product::query()->sum('stock'),
                'en_rupture' => Product::query()->enRupture()->count(),
            ],
            // Carousel des produits disponibles + quantités (pas de valeur monétaire).
            'disponibles' => Product::query()
                ->where('is_active', true)
                ->where('stock', '>', 0)
                ->orderByDesc('stock')
                ->limit(20)
                ->get(),
            'alertes' => Product::query()->enRupture()->orderBy('stock')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function genericData(): array
    {
        return [
            'kpis' => [
                'commandes_en_attente' => Order::query()->enAttente()->count(),
                'en_rupture' => Product::query()->enRupture()->count(),
                'rapports_du_jour' => TerrainReport::query()->whereDate('date', today())->count(),
            ],
            'pendingOrders' => Order::query()
                ->enAttente()
                ->with(['client', 'user'])
                ->latest('date_commande')
                ->limit(10)
                ->get(),
        ];
    }
}
