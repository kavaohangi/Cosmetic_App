<x-app-layout title="Nouveau bon de livraison">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Nouveau bon de livraison</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Créez un bon de livraison à partir d'une commande validée (stock réservé).</p>
            <a href="{{ route('deliveries.index') }}" class="btn-secondary">Retour</a>
        </div>

        @if ($orders->isEmpty())
            <div class="card text-center py-12 text-gray-400">Aucune commande validée en attente de livraison.</div>
        @else
            <form method="POST" action="{{ route('deliveries.store') }}" class="card space-y-5">
                @csrf

                <div>
                    <label class="form-label" for="order_id">Commande validée</label>
                    <select id="order_id" name="order_id" class="form-input" required>
                        <option value="">-- Sélectionner une commande --</option>
                        @foreach ($orders as $order)
                            <option value="{{ $order->id }}">
                                {{ $order->reference }} — {{ $order->client?->name }} ({{ $order->items->count() }} produit(s), @money((float) $order->total))
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label" for="type_vente">Type de vente</label>
                    <select id="type_vente" name="type_vente" class="form-input" required>
                        @foreach (\App\Enums\SaleType::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Crédit ou comptant — séparé dans la clôture journalière.</p>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary">Créer le bon de livraison</button>
                </div>
            </form>
        @endif
    </div>
</x-app-layout>
