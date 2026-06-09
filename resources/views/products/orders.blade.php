<x-app-layout title="Stock vs Commandes">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Stock vs Commandes</h1>
    </x-slot>

    <div class="space-y-6">
        <p class="text-sm text-gray-500">Vue simultanée par produit : commandes en cours et stock disponible.</p>

        <div class="card p-0 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Produit</th>
                        <th class="px-6 py-3">Nb commandes</th>
                        <th class="px-6 py-3">Qté commandée</th>
                        <th class="px-6 py-3">Stock physique</th>
                        <th class="px-6 py-3">Disponible</th>
                        <th class="px-6 py-3">État</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rows as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $row['product']->name }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $row['nb_commandes'] }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $row['quantite_commandee'] }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $row['stock'] }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $row['disponible'] }}</td>
                            <td class="px-6 py-4">
                                @if ($row['quantite_commandee'] > $row['disponible'])
                                    <span class="badge badge-red">Insuffisant</span>
                                @elseif ($row['nb_commandes'] > 0)
                                    <span class="badge badge-green">Couvert</span>
                                @else
                                    <span class="badge badge-gray">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Aucun produit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
