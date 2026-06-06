<x-app-layout title="Client">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Client</h1>
    </x-slot>


    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $client->name }}</h1>
                <p class="text-sm text-gray-500">{{ ucfirst($client->type ?? '') }} · {{ $client->ville }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('clients.index') }}" class="btn-secondary">Retour</a>
                @can('update', $client)
                    <a href="{{ route('clients.edit', $client) }}" class="btn-primary">Modifier</a>
                @endcan
            </div>
        </div>

        <div class="card grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <p class="text-xs uppercase text-gray-400 font-medium">Email</p>
                <p class="mt-1 text-gray-900">{{ $client->email ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-400 font-medium">Téléphone</p>
                <p class="mt-1 text-gray-900">{{ $client->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-400 font-medium">Adresse</p>
                <p class="mt-1 text-gray-900">{{ $client->address ?? '—' }}</p>
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Commandes</h2></div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Référence</th>
                        <th class="px-6 py-3">Montant</th>
                        <th class="px-6 py-3">Statut</th>
                        <th class="px-6 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($client->orders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <a href="{{ route('orders.show', $order) }}" class="hover:text-[#6366F1]">{{ $order->reference }}</a>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ number_format((float) $order->total, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-gray-600">{{ $order->statut->label() }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $order->date_commande?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">Aucune commande.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
