<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Http\Requests\StoreTerrainReportRequest;
use App\Models\Client;
use App\Models\Product;
use App\Models\TerrainReport;
use App\Models\User;
use App\Services\TerrainService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TerrainController extends Controller
{
    public function __construct(private TerrainService $terrainService) {}

    public function index(Request $request): View
    {
        $reports = $request->user()
            ->terrainReports()
            ->with(['supervisor', 'items'])
            ->latest('date')
            ->paginate(15);

        return view('terrain.index', ['reports' => $reports]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', TerrainReport::class);

        return view('terrain.create', [
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'clients' => Client::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTerrainReportRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $items = $data['items'];

        $report = DB::transaction(function () use ($user, $data, $items): TerrainReport {
            $totalQuantite = 0;

            $report = $user->terrainReports()->create([
                'date' => $data['date'] ?? today(),
                'supervisor_id' => $user->supervisor_id,
                'nb_ventes' => 0,
            ]);

            foreach ($items as $line) {
                $sousTotal = (float) $line['prix_unitaire'] * (int) $line['quantite'];
                $totalQuantite += (int) $line['quantite'];

                $report->items()->create([
                    'product_id' => $line['product_id'],
                    'client_id' => $line['client_id'] ?? null,
                    'quantite' => $line['quantite'],
                    'prix_unitaire' => $line['prix_unitaire'],
                    'sous_total' => $sousTotal,
                ]);
            }

            $report->update(['nb_ventes' => $totalQuantite]);

            return $report;
        });

        return redirect()
            ->route('terrain.show', $report)
            ->with('status', 'Rapport terrain soumis.');
    }

    public function show(TerrainReport $terrain): View
    {
        Gate::authorize('view', $terrain);

        $terrain->load(['user', 'supervisor', 'items.product', 'items.client']);

        return view('terrain.show', ['report' => $terrain]);
    }

    /**
     * Consolidated team view: members + their monthly performance + recent reports.
     */
    public function team(Request $request): View
    {
        Gate::authorize('viewTeam', TerrainReport::class);

        $user = $request->user();
        $isAgentsView = in_array($user->role, [Role::ChefMarketing, Role::Directeur, Role::Admin], true);

        $startOfMonth = now()->startOfMonth();

        $membersQuery = $isAgentsView
            ? User::query()->withRole(Role::AgentMarketeur)
            : $user->terrains();

        /** @var HasMany|Builder $membersQuery */
        $members = $membersQuery
            ->orderBy('name')
            ->get()
            ->map(function (User $member) use ($startOfMonth): array {
                $monthlyOrders = $member->orders()
                    ->whereDate('date_commande', '>=', $startOfMonth);

                return [
                    'user' => $member,
                    'orders_count' => (clone $monthlyOrders)->count(),
                    'ca' => (float) (clone $monthlyOrders)
                        ->whereIn('statut', [OrderStatus::Validee->value, OrderStatus::Livree->value])
                        ->sum('total'),
                ];
            });

        return view('terrain.team', [
            'members' => $members,
            'isAgentsView' => $isAgentsView,
            'reports' => $this->terrainService->getMyTeamReports($user->id),
        ]);
    }
}
