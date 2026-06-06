<x-app-layout title="Alertes de stock">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Alertes de stock</h1>
    </x-slot>

@php
    $badge = fn (\App\Enums\StockAlertStatus $s) => match ($s) {
        \App\Enums\StockAlertStatus::EnAttente => 'badge-red',
        \App\Enums\StockAlertStatus::Resolu => 'badge-green',
    };
    $canResolve = in_array(auth()->user()->role, [\App\Enums\Role::Magasinier, \App\Enums\Role::Admin], true);
@endphp

    <div class="space-y-6">
        {{-- Filtres --}}
        <form method="GET" action="{{ route('stock-alerts.index') }}" class="card">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Produit</label>
                    <select name="product_id" class="form-input w-full">
                        <option value="">Tous</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" @selected(($filters['product_id'] ?? null) == $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
                    <select name="statut" class="form-input w-full">
                        <option value="">Tous</option>
                        @foreach ($statuts as $s)
                            <option value="{{ $s->value }}" @selected(($filters['statut'] ?? null) === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Du</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-input w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Au</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-input w-full">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary">Filtrer</button>
                    <a href="{{ route('stock-alerts.index') }}" class="btn-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Produit</th>
                            <th class="px-6 py-3">Commande</th>
                            <th class="px-6 py-3">Demandé / Dispo</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Statut</th>
                            <th class="px-6 py-3">Résolu par</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($alerts as $alert)
                            <tr class="hover:bg-gray-50 {{ $alert->statut === \App\Enums\StockAlertStatus::EnAttente ? 'bg-red-50/40' : '' }}">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $alert->product?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">
                                    @if ($alert->order)
                                        <a href="{{ route('orders.show', $alert->order) }}" class="text-[#6366F1] hover:underline">{{ $alert->order->reference }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-600">{{ $alert->quantite_demandee }} / {{ $alert->quantite_disponible }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $alert->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4"><span class="badge {{ $badge($alert->statut) }}">{{ $alert->statut->label() }}</span></td>
                                <td class="px-6 py-4 text-gray-500">
                                    @if ($alert->resolver)
                                        {{ $alert->resolver->name }}<br>
                                        <span class="text-xs text-gray-400">{{ $alert->resolved_at?->format('d/m/Y H:i') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($canResolve && $alert->statut === \App\Enums\StockAlertStatus::EnAttente)
                                        <form method="POST" action="{{ route('stock-alerts.resolve', $alert) }}">
                                            @csrf @method('PATCH')
                                            <button class="btn-primary !py-1.5 !px-3 text-xs">Confirmer la dispo</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">Aucune alerte de stock.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $alerts->links() }}</div>
    </div>
</x-app-layout>
