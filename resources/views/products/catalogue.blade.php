<x-app-layout title="Catalogue produits">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Catalogue produits</h1>
    </x-slot>

    <div class="space-y-6">
        <p class="text-sm text-gray-500">{{ $products->total() }} produit(s) disponible(s) à la vente.</p>

        @if ($products->isEmpty())
            <div class="card text-center py-12 text-gray-400">Aucun produit disponible pour le moment.</div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach ($products as $product)
                    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm hover:shadow-md transition">
                        <div class="aspect-square bg-gray-100 flex items-center justify-center">
                            @if ($product->image_url)
                                <img src="{{ \Illuminate\Support\Str::startsWith($product->image_url, 'http') ? $product->image_url : \Illuminate\Support\Facades\Storage::url($product->image_url) }}"
                                     alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                            @endif
                        </div>
                        <div class="p-4">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $product->name }}</p>
                            @if ($product->category)
                                <p class="mt-1 text-xs text-gray-400">{{ $product->category }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $products->links() }}</div>
        @endif
    </div>
</x-app-layout>
