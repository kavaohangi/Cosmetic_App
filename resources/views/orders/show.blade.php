<x-app-layout title="Détail commande">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Détail commande</h1>
    </x-slot>

@php
    $badge = fn (\App\Enums\OrderStatus $s) => match ($s) {
        \App\Enums\OrderStatus::EnAttente => 'badge-orange',
        \App\Enums\OrderStatus::Validee, \App\Enums\OrderStatus::EnPreparation => 'badge-indigo',
        \App\Enums\OrderStatus::Livree => 'badge-green',
        \App\Enums\OrderStatus::Annulee => 'badge-red',
    };

    $steps = [
        ['key' => 'soumise', 'label' => 'Soumise', 'done' => true],
        ['key' => 'en_attente', 'label' => 'En attente', 'done' => in_array($order->statut, [\App\Enums\OrderStatus::EnAttente, \App\Enums\OrderStatus::Validee, \App\Enums\OrderStatus::EnPreparation, \App\Enums\OrderStatus::Livree])],
        ['key' => 'validee', 'label' => 'Validée', 'done' => in_array($order->statut, [\App\Enums\OrderStatus::Validee, \App\Enums\OrderStatus::EnPreparation, \App\Enums\OrderStatus::Livree])],
        ['key' => 'livree', 'label' => 'Livrée', 'done' => $order->statut === \App\Enums\OrderStatus::Livree],
    ];
    if ($order->statut === \App\Enums\OrderStatus::Annulee) {
        $steps = [
            ['key' => 'soumise', 'label' => 'Soumise', 'done' => true],
            ['key' => 'refusee', 'label' => 'Refusée', 'done' => true],
        ];
    }
@endphp

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $order->reference }}</h1>
                <p class="text-sm text-gray-500">Commande du {{ $order->date_commande?->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('orders.index') }}" class="btn-secondary">Retour</a>
        </div>

        @if (!empty($manquants))
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-medium">Stock insuffisant :</p>
                <ul class="list-disc list-inside mt-1">
                    @foreach ($manquants as $m)
                        <li>{{ $m['name'] }} — demandé {{ $m['demande'] }}, disponible {{ $m['disponible'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Timeline de suivi --}}
        <div class="card">
            <p class="text-xs uppercase text-gray-400 font-medium mb-4">Suivi de la commande</p>
            <div class="flex items-center gap-2">
                @foreach ($steps as $i => $step)
                    @php
                        $isLast = $loop->last;
                        $color = $step['done'] ? 'bg-[#6366F1] border-[#6366F1] text-white' : 'bg-white border-gray-300 text-gray-400';
                    @endphp
                    <div class="flex items-center gap-2 {{ $isLast ? '' : 'flex-1' }}">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full border-2 text-xs font-bold shrink-0 {{ $color }}">
                            {{ $i + 1 }}
                        </div>
                        <span class="text-sm font-medium {{ $step['done'] ? 'text-gray-900' : 'text-gray-400' }}">{{ $step['label'] }}</span>
                        @if (!$isLast)
                            <div class="h-0.5 flex-1 mx-2 {{ $steps[$i+1]['done'] ?? false ? 'bg-[#6366F1]' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Client</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $order->client?->name }}</p>
                <p class="text-sm text-gray-500">{{ $order->client?->ville }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Statut</p>
                <p class="mt-2"><span class="badge {{ $badge($order->statut) }}">{{ $order->statut->label() }}</span></p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Total</p>
                <p class="mt-1 text-xl font-bold text-gray-900">{{ number_format((float) $order->total, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>

        {{-- Traçabilité --}}
        <div class="card">
            <p class="text-xs uppercase text-gray-400 font-medium mb-3">Traçabilité</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Créée par :</span>
                    <span class="font-medium text-gray-900">{{ $order->user?->name ?? '—' }}</span>
                    <span class="text-gray-400">le {{ $order->created_at?->format('d/m/Y H:i') }}</span>
                </div>
                <div>
                    @if ($order->traite_par)
                        <span class="text-gray-500">{{ $order->statut === \App\Enums\OrderStatus::Annulee ? 'Refusée par :' : 'Validée par :' }}</span>
                        <span class="font-medium text-gray-900">{{ $order->validator?->name ?? '—' }}</span>
                        <span class="text-gray-400">le {{ $order->traite_le?->format('d/m/Y H:i') }}</span>
                    @else
                        <span class="text-gray-400">En attente de décision du Chef Marketing.</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Articles</h2></div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Produit</th>
                        <th class="px-6 py-3">Quantité</th>
                        <th class="px-6 py-3">Prix unitaire</th>
                        <th class="px-6 py-3 text-right">Sous-total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $item->product?->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $item->quantite }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ number_format((float) $item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900">{{ number_format((float) $item->sous_total, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($order->statut === \App\Enums\OrderStatus::EnAttente)
            <div class="flex justify-end gap-3">
                @can('reject', $order)
                    <form method="POST" action="{{ route('orders.reject', $order) }}">
                        @csrf @method('PATCH')
                        <button class="btn-danger">Refuser</button>
                    </form>
                @endcan
                @can('validate', $order)
                    <form method="POST" action="{{ route('orders.validate', $order) }}">
                        @csrf @method('PATCH')
                        <button class="btn-primary">Valider la commande</button>
                    </form>
                @endcan
            </div>
        @endif
    </div>
</x-app-layout>
