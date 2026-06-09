<x-app-layout title="Livraisons">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Bons de livraison</h1>
    </x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">{{ $deliveries->total() }} livraison(s).</p>
            @can('create', \App\Models\Delivery::class)
                <a href="{{ route('deliveries.create') }}" class="btn-primary">+ Nouveau bon de livraison</a>
            @endcan
        </div>

        <div class="card p-0 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Référence</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Client</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Statut</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($deliveries as $delivery)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $delivery->reference }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $delivery->date?->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $delivery->client?->name ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="badge {{ $delivery->type_vente === \App\Enums\SaleType::Credit ? 'badge-orange' : 'badge-green' }}">{{ $delivery->type_vente->label() }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($delivery->statut === \App\Enums\DeliveryStatus::Livre)
                                    <span class="badge badge-green">{{ $delivery->statut->label() }}</span>
                                @elseif ($delivery->statut === \App\Enums\DeliveryStatus::Annule)
                                    <span class="badge badge-red">{{ $delivery->statut->label() }}</span>
                                @else
                                    <span class="badge badge-gray">{{ $delivery->statut->label() }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('deliveries.show', $delivery) }}" class="text-sm font-medium text-[#6366F1] hover:underline">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Aucune livraison.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $deliveries->links() }}</div>
    </div>
</x-app-layout>
