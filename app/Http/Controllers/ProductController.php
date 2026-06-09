<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ProduitReapprovisionne;
use App\Services\InventoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

class ProductController extends Controller
{
    public function __construct(private InventoryService $inventory) {}

    public function index(Request $request): View
    {
        // Catalogue épuré pour l'agent terrain : noms + photos uniquement,
        // sans aucune information de stock ni de rupture.
        if ($request->user()->role === Role::MarketeurTerrain) {
            return view('products.catalogue', [
                'products' => Product::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->paginate(24),
            ]);
        }

        $products = Product::query()
            ->when($request->boolean('rupture'), fn ($q) => $q->enRupture())
            ->orderBy('name')
            ->paginate(20);

        // Produits en rupture MAIS commandés (commandes en attente / validées).
        $ruptureCommandes = Product::query()
            ->enRupture()
            ->whereHas('orderItems.order', fn ($q) => $q->whereIn('statut', ['en_attente', 'validee']))
            ->withCount(['orderItems as commandes_count' => fn ($q) => $q->whereHas('order', fn ($o) => $o->whereIn('statut', ['en_attente', 'validee']))])
            ->orderBy('name')
            ->get();

        return view('products.index', [
            'products' => $products,
            'ruptureCommandes' => $ruptureCommandes,
        ]);
    }

    /**
     * Magasinier view: stock vs. orders per product (simultaneous view).
     */
    public function orders(): View
    {
        Gate::authorize('viewAny', Product::class);

        $rows = Product::query()
            ->withCount([
                'orderItems as commandes_count' => fn ($q) => $q->whereHas('order', fn ($o) => $o->whereIn('statut', ['en_attente', 'validee'])),
            ])
            ->with(['orderItems' => fn ($q) => $q->whereHas('order', fn ($o) => $o->whereIn('statut', ['en_attente', 'validee']))])
            ->orderBy('name')
            ->get()
            ->map(fn (Product $p) => [
                'product' => $p,
                'nb_commandes' => $p->commandes_count,
                'quantite_commandee' => (int) $p->orderItems->sum('quantite'),
                'stock' => $p->stock,
                'disponible' => $p->disponible,
            ]);

        return view('products.orders', ['rows' => $rows]);
    }

    /**
     * Adjust a product's physical stock (inventory correction) and notify the
     * Chef Marketing when a previously out-of-stock + ordered product is back.
     */
    public function adjust(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $data = $request->validate([
            'stock' => ['required', 'integer', 'min:0'],
            'motif' => ['nullable', 'string', 'max:255'],
        ]);

        $etaitEnRupture = $product->estEnRupture();
        $avaitCommandes = $product->orderItems()
            ->whereHas('order', fn ($q) => $q->whereIn('statut', ['en_attente', 'validee']))
            ->exists();

        $this->inventory->adjust($product, (int) $data['stock'], $request->user()->id, $data['motif'] ?? null);
        $product->refresh();

        if ($etaitEnRupture && $avaitCommandes && ! $product->estEnRupture()) {
            $chefs = User::query()
                ->where('role', Role::ChefMarketing->value)
                ->where('is_active', true)
                ->get();

            if ($chefs->isNotEmpty()) {
                Notification::send($chefs, new ProduitReapprovisionne($product, $product->stock));
            }
        }

        return back()->with('status', 'Stock ajusté.');
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);

        return view('products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->validated());

        return redirect()
            ->route('products.show', $product)
            ->with('status', 'Produit créé.');
    }

    public function show(Product $product): View
    {
        return view('products.show', ['product' => $product]);
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        return view('products.edit', ['product' => $product]);
    }

    public function update(StoreProductRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $product->update($request->validated());

        return redirect()
            ->route('products.show', $product)
            ->with('status', 'Produit mis à jour.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Produit supprimé.');
    }
}
