<x-app-layout title="Nouvelle offre">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Nouvelle offre</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Créer une offre</h1>
            <a href="{{ route('offers.index') }}" class="btn-secondary">Retour</a>
        </div>

        <form method="POST" action="{{ route('offers.store') }}" class="card space-y-5">
            @csrf
            <div>
                <label class="form-label" for="titre">Titre</label>
                <input id="titre" type="text" name="titre" value="{{ old('titre') }}" class="form-input" required>
                @error('titre')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label" for="product_id">Produit concerné (optionnel)</label>
                <select id="product_id" name="product_id" class="form-input">
                    <option value="">— Tous / aucun —</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label" for="remise_pourcentage">Remise (%)</label>
                <input id="remise_pourcentage" type="number" step="0.01" min="0" max="100" name="remise_pourcentage" value="{{ old('remise_pourcentage') }}" class="form-input">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label" for="date_debut">Début</label>
                    <input id="date_debut" type="date" name="date_debut" value="{{ old('date_debut') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label" for="date_fin">Fin</label>
                    <input id="date_fin" type="date" name="date_fin" value="{{ old('date_fin') }}" class="form-input">
                    @error('date_fin')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="form-label" for="description">Description</label>
                <textarea id="description" name="description" rows="3" class="form-input">{{ old('description') }}</textarea>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="actif" value="1" checked class="rounded border-gray-300">
                Offre active
            </label>
            <div class="pt-2">
                <button type="submit" class="btn-primary">Créer l'offre</button>
            </div>
        </form>
    </div>
</x-app-layout>
