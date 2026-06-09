<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\CommandeSoumise;
use App\Notifications\CommandeTraitee;
use App\Notifications\StockInsuffisant;
use App\Services\InventoryService;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private InventoryService $inventoryService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $orders = Order::query()
            ->when($request->boolean('en_attente'), fn ($q) => $q->enAttente())
            ->when(
                $user->role === Role::AgentMarketeur || $user->role === Role::MarketeurTerrain,
                fn ($q) => $q->where('user_id', $user->id)
            )
            ->with(['client', 'user'])
            ->latest('date_commande')
            ->paginate(20);

        return view('orders.index', ['orders' => $orders]);
    }

    public function create(): View
    {
        Gate::authorize('create', Order::class);

        return view('orders.create', [
            'clients' => Client::query()->orderBy('name')->get(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Confirmation de rupture au panier : si un produit est indisponible et
        // que l'agent n'a pas explicitement confirmé, on revient avec la liste.
        $rupture = $this->orderService->checkAvailabilityForItems($data['items']);

        if ($rupture !== [] && ! $request->boolean('confirm_rupture')) {
            return back()
                ->withInput()
                ->with('rupture_confirmation', $rupture)
                ->with('warning', 'Certains produits sont en rupture. Confirmez pour soumettre la commande malgré tout.');
        }

        $order = DB::transaction(function () use ($data, $request): Order {
            $order = Order::create([
                'reference' => 'CMD-'.strtoupper(Str::random(8)),
                'client_id' => $data['client_id'],
                'user_id' => $request->user()->id,
                'statut' => OrderStatus::EnAttente,
                'total' => 0,
                'date_commande' => $data['date_commande'] ?? today(),
                'notes' => $data['notes'] ?? null,
            ]);

            $total = 0;

            foreach ($data['items'] as $line) {
                $product = Product::findOrFail($line['product_id']);
                $sousTotal = $product->price * $line['quantite'];
                $total += $sousTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantite' => $line['quantite'],
                    'prix_unitaire' => $product->price,
                    'sous_total' => $sousTotal,
                ]);
            }

            $order->update(['total' => $total]);

            return $order;
        });

        // L'agent ayant confirmé, les produits en rupture restent dans la
        // commande : on trace les alertes et on prévient la production.
        if ($rupture !== []) {
            $this->orderService->recordStockAlerts($order, $rupture, $request->user()->id);
            $this->notifyStockManagers($order, $rupture);
        }

        // Notifier le Chef Marketing (supervisor) de la soumission
        $chef = $order->user?->supervisor;
        if ($chef) {
            $chef->notify(new CommandeSoumise($order));
        }

        if ($rupture !== []) {
            $noms = implode(', ', array_column($rupture, 'name'));

            return redirect()
                ->route('orders.show', $order)
                ->with('warning', "Commande soumise au Chef Marketing avec produit(s) en rupture (production alertée) : {$noms}.");
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Commande créée et soumise au Chef Marketing.');
    }

    /**
     * Notify stock managers (production) about products in rupture.
     *
     * @param  array<int, array{product_id:int, name:string, demande:int, disponible:int}>  $manquants
     */
    private function notifyStockManagers(Order $order, array $manquants): void
    {
        $stockManagers = User::query()
            ->whereIn('role', [Role::Magasinier->value, Role::Directeur->value, Role::Admin->value])
            ->where('is_active', true)
            ->get();

        if ($stockManagers->isNotEmpty()) {
            Notification::send($stockManagers, new StockInsuffisant($order, $manquants));
        }
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['client', 'user', 'items.product']);

        return view('orders.show', [
            'order' => $order,
            'manquants' => $this->orderService->checkStock($order),
        ]);
    }

    /**
     * Validate an order: decrement stock and flag rupture.
     */
    public function validateOrder(Order $order): RedirectResponse
    {
        Gate::authorize('validate', $order);

        if ($order->statut !== OrderStatus::EnAttente) {
            return back()->with('warning', 'Cette commande a déjà été traitée.');
        }

        $requestUser = request()->user();

        // Retirer du panier les produits devenus indisponibles avant validation
        $manquants = $this->orderService->pruneUnavailableItems($order);

        if ($manquants !== []) {
            $this->orderService->recordStockAlerts($order, $manquants, $requestUser?->id);

            // Notifier le Chef Marketing (celui qui valide) + la production
            if ($requestUser) {
                $requestUser->notify(new StockInsuffisant($order, $manquants));
            }
            $this->notifyStockManagers($order, $manquants);

            // Notifier l'agent du retrait des produits en rupture
            if ($order->user) {
                $order->user->notify(new CommandeTraitee($order, 'stock_insufficient'));
            }
        }

        // Si tous les produits ont été retirés, rien à valider
        if ($order->items()->count() === 0) {
            return back()->with('warning', 'Validation impossible : tous les produits sont en rupture. Les responsables ont été alertés.');
        }

        // Réserver (bloquer) le stock disponible sans toucher au stock physique.
        // Le stock physique ne sera déduit qu'à la livraison / bon de sortie.
        $reserveManquants = $this->inventoryService->reserveForOrder($order, $requestUser?->id);

        if ($reserveManquants !== []) {
            $this->orderService->recordStockAlerts($order, $reserveManquants, $requestUser?->id);
            $this->notifyStockManagers($order, $reserveManquants);

            $noms = implode(', ', array_column($reserveManquants, 'name'));

            return back()->with('warning', "Réservation impossible : stock disponible insuffisant pour {$noms}. Les responsables stock ont été alertés.");
        }

        $order->update([
            'statut' => OrderStatus::Validee,
            'traite_par' => $requestUser?->id,
            'traite_le' => now(),
        ]);

        // Notifier l'agent que sa commande a été validée
        if ($order->user) {
            $order->user->notify(new CommandeTraitee($order, 'validated'));
        }

        $this->orderService->notifyRupture();

        if ($manquants !== []) {
            $noms = implode(', ', array_column($manquants, 'name'));

            return back()->with('warning', "Commande validée partiellement. Produit(s) retiré(s) pour rupture : {$noms}.");
        }

        return back()->with('status', 'Commande validée.');
    }

    /**
     * Refuse a pending order.
     */
    public function rejectOrder(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('reject', $order);

        if ($order->statut !== OrderStatus::EnAttente) {
            return back()->with('warning', 'Cette commande a déjà été traitée.');
        }

        $data = $request->validate([
            'motif_rejet' => ['nullable', 'string', 'max:1000'],
        ]);

        // Au cas où une réservation existait déjà, on la libère.
        $this->inventoryService->releaseForOrder($order, $request->user()?->id);

        $order->update([
            'statut' => OrderStatus::Annulee,
            'traite_par' => $request->user()?->id,
            'traite_le' => now(),
            'motif_rejet' => $data['motif_rejet'] ?? null,
        ]);

        // Notifier l'agent que sa commande a été refusée
        if ($order->user) {
            $order->user->notify(new CommandeTraitee($order, 'rejected'));
        }

        return back()->with('status', 'Commande refusée.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        Gate::authorize('delete', $order);

        $order->delete();

        return redirect()
            ->route('orders.index')
            ->with('status', 'Commande supprimée.');
    }
}
