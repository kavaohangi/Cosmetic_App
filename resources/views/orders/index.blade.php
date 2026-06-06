<x-app-layout title="Commandes">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Commandes</h1>
    </x-slot>

@php
    $badge = fn (\App\Enums\OrderStatus $s) => match ($s) {
        \App\Enums\OrderStatus::EnAttente => 'badge-orange',
        \App\Enums\OrderStatus::Validee, \App\Enums\OrderStatus::EnPreparation => 'badge-indigo',
        \App\Enums\OrderStatus::Livree => 'badge-green',
        \App\Enums\OrderStatus::Annulee => 'badge-red',
    };
@endphp

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">{{ $orders->total() }} commande(s) au total.</p>
            @can('create', \App\Models\Order::class)
                <a href="{{ route('orders.create') }}" class="btn-primary">+ Nouvelle commande</a>
            @endcan
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Référence</th>
                            <th class="px-6 py-3">Client</th>
                            <th class="px-6 py-3">Créée par</th>
                            <th class="px-6 py-3">Statut</th>
                            <th class="px-6 py-3">Montant</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($orders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $order->reference }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $order->client?->name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $order->user?->name }}</td>
                                <td class="px-6 py-4"><span class="badge {{ $badge($order->statut) }}">{{ $order->statut->label() }}</span></td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ number_format((float) $order->total, 0, ',', ' ') }} FCFA</td>
                                <td class="px-6 py-4 text-gray-500">{{ $order->date_commande?->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('orders.show', $order) }}" class="text-sm font-medium text-[#6366F1] hover:underline">Détails</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">Aucune commande.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $orders->links() }}</div>
    </div>
</x-app-layout>
