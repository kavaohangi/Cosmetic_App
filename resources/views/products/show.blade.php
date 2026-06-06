<x-app-layout title="Produit">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Produit</h1>
    </x-slot>
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
            <a href="{{ route('products.index') }}" class="btn-secondary">Retour</a>
        </div>

        <div class="card grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <p class="text-xs uppercase text-gray-400 font-medium">SKU</p>
                <p class="mt-1 text-gray-900">{{ $product->sku }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-400 font-medium">Catégorie</p>
                <p class="mt-1 text-gray-900">{{ $product->category ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-400 font-medium">Prix</p>
                <p class="mt-1 text-gray-900">{{ number_format((float) $product->price, 0, ',', ' ') }} FCFA</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-400 font-medium">Stock</p>
                <p class="mt-1">
                    @if ($product->estEnRupture())
                        <span class="badge badge-red">{{ $product->stock }} · rupture</span>
                    @else
                        <span class="badge badge-green">{{ $product->stock }}</span>
                    @endif
                </p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-xs uppercase text-gray-400 font-medium">Description</p>
                <p class="mt-1 text-gray-700">{{ $product->description ?? '—' }}</p>
            </div>
        </div>

        @can('update', $product)
            <a href="{{ route('products.edit', $product) }}" class="btn-primary">Ajuster le stock</a>
        @endcan
    </div>
</x-app-layout>
