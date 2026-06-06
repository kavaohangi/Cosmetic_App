<x-app-layout title="Ajuster le stock">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Ajuster le stock</h1>
    </x-slot>
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
            <a href="{{ route('products.index') }}" class="btn-secondary">Retour</a>
        </div>

        <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('products.partials.form', ['product' => $product])
            <div class="flex justify-end gap-3">
                <a href="{{ route('products.index') }}" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>

        @can('delete', $product)
            <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Supprimer ce produit ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">Supprimer le produit</button>
            </form>
        @endcan
    </div>
</x-app-layout>
