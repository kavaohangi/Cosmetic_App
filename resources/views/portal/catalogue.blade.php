<x-app-layout title="Catalogue">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Catalogue produits</h1>
    </x-slot>

    <div class="max-w-6xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Découvrez nos produits disponibles.</p>
            <div class="flex gap-2">
                <a href="{{ route('portal.orders') }}" class="btn-secondary">Mes commandes</a>
                <a href="{{ route('portal.offers') }}" class="btn-secondary">Offres</a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5">
            @forelse ($products as $product)
                <div class="card">
                    <div class="aspect-square rounded-lg bg-gray-100 mb-3 flex items-center justify-center overflow-hidden">
                        @if ($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="object-cover w-full h-full">
                        @else
                            <span class="text-gray-300 text-3xl">{{ mb_substr($product->name, 0, 1) }}</span>
                        @endif
                    </div>
                    <p class="font-semibold text-gray-900 truncate">{{ $product->name }}</p>
                    <p class="text-sm text-gray-500">@money((float) $product->price)</p>
                </div>
            @empty
                <p class="col-span-full text-center text-gray-400 py-10">Aucun produit disponible.</p>
            @endforelse
        </div>

        {{ $products->links() }}
    </div>
</x-app-layout>
