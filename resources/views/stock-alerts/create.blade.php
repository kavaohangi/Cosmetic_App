<x-app-layout title="Signaler une rupture">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Signaler une rupture</h1>
    </x-slot>

    <div class="max-w-xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Transmettre une alerte de rupture au Chef Marketing.</p>
            <a href="{{ route('stock-alerts.index') }}" class="btn-secondary">Retour</a>
        </div>

        <form method="POST" action="{{ route('stock-alerts.store') }}" class="card space-y-5">
            @csrf

            <div>
                <label class="form-label" for="product_id">Produit</label>
                <select id="product_id" name="product_id" class="form-input" required>
                    <option value="">-- Sélectionner --</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (stock {{ $product->stock }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label" for="description">Description (facultatif)</label>
                <textarea id="description" name="description" rows="3" class="form-input" placeholder="Détails sur la rupture..."></textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary">Transmettre au Chef Marketing</button>
            </div>
        </form>
    </div>
</x-app-layout>
