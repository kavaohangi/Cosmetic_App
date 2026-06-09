<x-app-layout title="Ajuster le stock">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Ajuster le stock</h1>
    </x-slot>
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
            <a href="{{ route('products.index') }}" class="btn-secondary">Retour</a>
        </div>

        {{-- Ajustement de stock journalisé (mouvement tracé) --}}
        <div class="card">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Ajustement de stock (journalisé)</h2>
            <p class="text-xs text-gray-500 mb-4">Stock physique actuel : <span class="font-medium">{{ $product->stock }}</span> · réservé : <span class="font-medium">{{ $product->stock_reserved }}</span> · disponible : <span class="font-medium">{{ $product->disponible }}</span></p>
            <form method="POST" action="{{ route('products.adjust', $product) }}" class="flex flex-col sm:flex-row sm:items-end gap-3">
                @csrf @method('PATCH')
                <div class="flex-1">
                    <label class="form-label" for="stock">Nouveau stock physique</label>
                    <input id="stock" type="number" min="0" name="stock" value="{{ $product->stock }}" class="form-input" required>
                </div>
                <div class="flex-1">
                    <label class="form-label" for="motif">Motif</label>
                    <input id="motif" type="text" name="motif" class="form-input" placeholder="Production, inventaire...">
                </div>
                <button type="submit" class="btn-primary">Ajuster</button>
            </form>
        </div>

        <form method="POST" action="{{ route('products.update', $product) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('products.partials.form', ['product' => $product])
            <div class="flex justify-end gap-3">
                <a href="{{ route('products.index') }}" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">Enregistrer la fiche</button>
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
