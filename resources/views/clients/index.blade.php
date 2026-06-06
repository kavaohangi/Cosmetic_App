<x-app-layout title="Clients">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Clients</h1>
    </x-slot>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">{{ $clients->total() }} client(s).</p>
            @can('create', \App\Models\Client::class)
                <a href="{{ route('clients.create') }}" class="btn-primary">+ Client</a>
            @endcan
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Nom</th>
                            <th class="px-6 py-3">Type</th>
                            <th class="px-6 py-3">Ville</th>
                            <th class="px-6 py-3">Téléphone</th>
                            <th class="px-6 py-3">Commandes</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($clients as $client)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $client->name }}</td>
                                <td class="px-6 py-4"><span class="badge badge-indigo">{{ $client->type ?? '—' }}</span></td>
                                <td class="px-6 py-4 text-gray-600">{{ $client->ville }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $client->phone }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $client->orders_count }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('clients.show', $client) }}" class="text-sm font-medium text-[#6366F1] hover:underline">Détails</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Aucun client.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $clients->links() }}</div>
    </div>
</x-app-layout>
