<x-app-layout title="Tableau de bord">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Tableau de bord</h1>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="card">
            <p class="text-sm text-gray-500">Bonjour <span class="font-semibold text-gray-900">{{ $client->name }}</span>, voici un aperçu de votre activité.</p>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Commandes</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $kpis['total_commandes'] }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">En cours</p>
                <p class="mt-1 text-2xl font-bold text-indigo-600">{{ $kpis['en_cours'] }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Livrées</p>
                <p class="mt-1 text-2xl font-bold text-green-600">{{ $kpis['livrees'] }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Offres actives</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $kpis['offres_actives'] }}</p>
            </div>
        </div>

        {{-- Mon marketeur --}}
        <div class="card flex items-center justify-between">
            <div>
                <p class="text-xs uppercase text-gray-400 font-medium">Mon marketeur</p>
                @if ($agent)
                    <p class="mt-1 font-semibold text-gray-900">{{ $agent->name }}</p>
                    <p class="text-sm text-gray-500">{{ $agent->email }}</p>
                @else
                    <p class="mt-1 text-sm text-gray-400">Aucun marketeur ne vous est encore assigné.</p>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('portal.marketeur') }}" class="btn-secondary">Voir la fiche</a>
                <a href="{{ route('portal.messages') }}" class="btn-primary relative">
                    Messages
                    @if ($unreadMessages > 0)
                        <span class="absolute -top-2 -right-2 inline-flex items-center justify-center h-5 min-w-5 px-1 rounded-full bg-red-500 text-[10px] font-bold text-white">{{ $unreadMessages }}</span>
                    @endif
                </a>
            </div>
        </div>

        {{-- Commandes récentes --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900">Commandes récentes</h2>
                <a href="{{ route('portal.orders') }}" class="text-sm font-medium text-[#6366F1] hover:underline">Tout voir</a>
            </div>
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
                    @forelse ($recentOrders as $order)
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
    </div>
</x-app-layout>
