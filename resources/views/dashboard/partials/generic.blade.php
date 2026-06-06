<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @include('partials.kpi', [
            'label' => 'Commandes en attente',
            'value' => $kpis['commandes_en_attente'],
            'tone' => 'orange',
            'valueTone' => 'orange',
            'hint' => 'En attente de validation',
            'icon' => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
        ])
        @include('partials.kpi', [
            'label' => 'Produits en rupture',
            'value' => $kpis['en_rupture'],
            'tone' => 'red',
            'valueTone' => 'red',
            'hint' => 'Sous le seuil d\'alerte',
            'icon' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>',
        ])
        @include('partials.kpi', [
            'label' => 'Rapports du jour',
            'value' => $kpis['rapports_du_jour'],
            'tone' => 'indigo',
            'hint' => 'Rapports terrain reçus',
            'icon' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v5h5"/>',
        ])
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Commandes en attente</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Référence</th>
                        <th class="px-6 py-3">Client</th>
                        <th class="px-6 py-3">Montant</th>
                        <th class="px-6 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pendingOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                <a href="{{ route('orders.show', $order) }}" class="hover:text-[#6366F1]">{{ $order->reference }}</a>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $order->client?->name }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ number_format((float) $order->total, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-gray-500">{{ $order->date_commande?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400">Aucune commande en attente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
