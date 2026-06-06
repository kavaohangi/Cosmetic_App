<x-app-layout title="Catalogue & Stock">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Catalogue &amp; Stock</h1>
    </x-slot>
    <div class="space-y-6">
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
                                <td class="px-6 py-4 font-medium text-gray-900">{{ number_format((float) $product->price, 0, ',', ' ') }} FCFA</td>
                                <td class="px-6 py-4">
                                    @if ($product->estEnRupture())
                                        <span class="badge badge-red">{{ $product->stock }} · rupture</span>
                                    @else
                                        <span class="badge badge-green">{{ $product->stock }}</span>
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
