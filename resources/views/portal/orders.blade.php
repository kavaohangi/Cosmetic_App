<x-app-layout title="Mes commandes">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Mes commandes</h1>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Bonjour {{ $client->name }}, suivez ici l'état de vos commandes.</p>
            <div class="flex gap-2">
                <a href="{{ route('portal.catalogue') }}" class="btn-secondary">Catalogue</a>
                <a href="{{ route('portal.offers') }}" class="btn-secondary">Offres</a>
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Référence</th>
                        <th class="px-6 py-3">Montant</th>
                        <th class="px-6 py-3">Statut</th>
                        <th class="px-6 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $order->reference }}</td>
                            <td class="px-6 py-4 text-gray-700">@money((float) $order->total)</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">{{ $order->statut->label() }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $order->date_commande?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400">Aucune commande pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links() }}
    </div>
</x-app-layout>
