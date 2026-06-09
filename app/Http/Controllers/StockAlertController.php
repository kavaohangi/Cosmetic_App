<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\StockAlertStatus;
use App\Models\Product;
use App\Models\StockAlert;
use App\Models\User;
use App\Notifications\AlerteRuptureMagasinier;
use App\Notifications\ProduitDisponible;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class StockAlertController extends Controller
{
    /**
     * List stock alerts with filters (product, status, date range).
     */
    public function index(Request $request): View
    {
        // Vue agents : uniquement les produits récemment renouvelés (résolus).
        if (in_array($request->user()->role, [Role::AgentMarketeur, Role::MarketeurTerrain], true)) {
            return $this->agentView();
        }

        $filters = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'statut' => ['nullable', 'string', 'in:'.implode(',', StockAlertStatus::values())],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $alerts = StockAlert::query()
            ->with(['product', 'order', 'creator', 'resolver'])
            ->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))
            ->when($filters['statut'] ?? null, fn ($q, $s) => $q->where('statut', $s))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('stock-alerts.index', [
            'alerts' => $alerts,
            'products' => Product::query()->orderBy('name')->get(),
            'filters' => $filters,
            'statuts' => StockAlertStatus::cases(),
        ]);
    }

    /**
     * Form for a Magasinier to manually raise a rupture alert.
     */
    public function create(Request $request): View
    {
        abort_unless($request->user()->role === Role::Magasinier, 403);

        return view('stock-alerts.create', [
            'products' => Product::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a Magasinier-raised rupture alert and notify the Chef Marketing.
     * Raising an alert is optional (not required for every rupture).
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === Role::Magasinier, 403);

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $alert = StockAlert::create([
            'product_id' => $data['product_id'],
            'created_by' => $request->user()->id,
            'description' => $data['description'] ?? null,
            'source' => 'magasinier',
            'statut' => StockAlertStatus::EnAttente,
        ]);

        $chefs = User::query()
            ->where('role', Role::ChefMarketing->value)
            ->where('is_active', true)
            ->get();

        if ($chefs->isNotEmpty()) {
            Notification::send($chefs, new AlerteRuptureMagasinier($alert->load('product')));
        }

        return redirect()
            ->route('stock-alerts.index')
            ->with('status', 'Alerte de rupture transmise au Chef Marketing.');
    }

    /**
     * Read-only view for marketeurs: products recently back in stock.
     */
    private function agentView(): View
    {
        $renewed = StockAlert::query()
            ->resolu()
            ->with(['product', 'resolver'])
            ->whereNotNull('resolved_at')
            ->where('resolved_at', '>=', now()->subDays(30))
            ->latest('resolved_at')
            ->get()
            ->unique('product_id')
            ->values();

        return view('stock-alerts.agent', [
            'renewed' => $renewed,
        ]);
    }

    /**
     * The stock manager confirms a product is back in stock ("passe au vert").
     * All agent marketeurs are then notified.
     */
    public function resolve(StockAlert $alert): RedirectResponse
    {
        if ($alert->statut === StockAlertStatus::Resolu) {
            return back()->with('warning', 'Cette alerte est déjà résolue.');
        }

        $alert->update([
            'statut' => StockAlertStatus::Resolu,
            'resolved_by' => request()->user()?->id,
            'resolved_at' => now(),
        ]);

        $alert->load(['product', 'resolver']);

        // Notifier tous les agents marketeurs (et marketeurs terrain) que le produit est dispo
        $agents = User::query()
            ->whereIn('role', [Role::AgentMarketeur->value, Role::MarketeurTerrain->value])
            ->where('is_active', true)
            ->get();

        if ($agents->isNotEmpty()) {
            Notification::send($agents, new ProduitDisponible($alert));
        }

        return back()->with('status', 'Disponibilité confirmée. Les agents ont été notifiés.');
    }
}
