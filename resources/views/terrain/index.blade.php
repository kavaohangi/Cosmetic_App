<x-app-layout title="Mes rapports">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Mes rapports</h1>
    </x-slot>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Mes rapports terrain</h1>
                <p class="text-sm text-gray-500">{{ $reports->total() }} rapport(s).</p>
            </div>
            @can('create', \App\Models\TerrainReport::class)
                <a href="{{ route('terrain.create') }}" class="btn-primary">+ Nouveau rapport</a>
            @endcan
        </div>

        <div class="card p-0 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Ventes</th>
                        <th class="px-6 py-3">Statut</th>
                        <th class="px-6 py-3">Envoyé à</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($reports as $report)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $report->date?->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-gray-900">{{ $report->nb_ventes }}</td>
                            <td class="px-6 py-4">
                                @if ($report->rupture_stock)
                                    <span class="badge badge-red">Rupture</span>
                                @elseif ($report->plaintes_clients)
                                    <span class="badge badge-orange">Plainte</span>
                                @else
                                    <span class="badge badge-green">RAS</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $report->supervisor?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('terrain.show', $report) }}" class="text-sm font-medium text-[#6366F1] hover:underline">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">Aucun rapport.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $reports->links() }}</div>
    </div>
</x-app-layout>
