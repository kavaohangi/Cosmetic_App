<div class="space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @include('partials.kpi', [
            'label' => 'Commandes à valider',
            'value' => $kpis['commandes_a_valider'],
            'tone' => 'orange',
            'valueTone' => 'orange',
            'hint' => 'En attente de validation',
            'icon' => '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
        ])
        @include('partials.kpi', [
            'label' => 'CA équipe du mois',
            'value' => number_format($kpis['ca_equipe'], 0, ',', ' ').' FCFA',
            'tone' => 'green',
            'hint' => 'Commandes validées & livrées',
            'hintTone' => 'green',
            'icon' => '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>',
        ])
        @include('partials.kpi', [
            'label' => 'Agents actifs',
            'value' => $kpis['agents_actifs'],
            'tone' => 'indigo',
            'hint' => 'Agents marketeurs',
            'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        ])
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Commandes en attente validation</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">N° Commande</th>
                        <th class="px-6 py-3">Agent</th>
                        <th class="px-6 py-3">Client</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Montant</th>
                        <th class="px-6 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pendingOrders as $order)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-gray-900">
                                <a href="{{ route('orders.show', $order) }}" class="hover:text-[#6366F1]">{{ $order->reference }}</a>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $order->user?->name }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $order->client?->name }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $order->date_commande?->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ number_format((float) $order->total, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('orders.validate', $order) }}">
                                        @csrf @method('PATCH')
                                        <button class="bg-[#6366F1] hover:bg-[#4F46E5] text-white text-xs font-semibold py-1.5 px-4 rounded-lg transition-colors">Valider</button>
                                    </form>
                                    <form method="POST" action="{{ route('orders.reject', $order) }}">
                                        @csrf @method('PATCH')
                                        <button class="bg-[#EF4444] hover:bg-red-600 text-white text-xs font-semibold py-1.5 px-4 rounded-lg transition-colors">Refuser</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">Aucune commande en attente.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
