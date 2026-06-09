<x-app-layout title="Catalogue & Stock">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Catalogue &amp; Stock</h1>
    </x-slot>
    <div class="space-y-6">
        @if (isset($ruptureCommandes) && $ruptureCommandes->isNotEmpty())
            <div class="rounded-xl border border-orange-200 bg-orange-50 overflow-hidden">
                <div class="px-6 py-4 border-b border-orange-200">
                    <h2 class="text-base font-semibold text-orange-800">En rupture mais commandés</h2>
                    <p class="text-xs text-orange-700">Produits sous le seuil avec des commandes client en cours — à réapprovisionner en priorité.</p>
                </div>
                <div class="divide-y divide-orange-100">
                    @foreach ($ruptureCommandes as $product)
                        <div class="flex items-center justify-between px-6 py-3 bg-white/60">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                <p class="text-xs text-gray-500">Stock {{ $product->stock }} · {{ $product->commandes_count }} commande(s)</p>
                            </div>
                            @can('update', $product)
                                <a href="{{ route('products.edit', $product) }}" class="btn-secondary !py-1.5 !px-3 text-xs">Ajuster</a>
                            @endcan
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">{{ $products->total() }} produit(s).</p>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.index', ['rupture' => request()->boolean('rupture') ? null : 1]) }}"
                   class="btn-secondary {{ request()->boolean('rupture') ? '!border-red-300 !text-red-600' : '' }}">
                    {{ request()->boolean('rupture') ? 'Tous les produits' : 'Voir ruptures' }}
                </a>
                @can('create', \App\Models\Product::class)
                    <a href="{{ route('products.create') }}" class="btn-primary">+ Produit</a>
                @endcan
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Produit</th>
                            <th class="px-6 py-3">SKU</th>
                            <th class="px-6 py-3">Catégorie</th>
                            <th class="px-6 py-3">Prix</th>
                            <th class="px-6 py-3">Stock</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($products as $product)
                            <tr class="hover:bg-gray-50 {{ $product->estEnRupture() ? 'bg-red-50/40' : '' }}">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $product->sku }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $product->category }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">@money((float) $product->price)</td>
                                <td class="px-6 py-4">
                                    @if ($product->estEnRupture())
                                        <span class="badge badge-red">{{ $product->stock }} · rupture</span>
                                    @else
                                        <span class="badge badge-green">{{ $product->stock }}</span>
                                    @endif
                                    @if ($product->stock_reserved > 0)
                                        <span class="badge badge-gray ml-1">{{ $product->stock_reserved }} réservé</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @can('update', $product)
                                        <a href="{{ route('products.edit', $product) }}" class="text-sm font-medium text-[#6366F1] hover:underline">Ajuster</a>
                                    @else
                                        <a href="{{ route('products.show', $product) }}" class="text-sm font-medium text-[#6366F1] hover:underline">Voir</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Aucun produit.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $products->links() }}</div>
    </div>
</x-app-layout>
