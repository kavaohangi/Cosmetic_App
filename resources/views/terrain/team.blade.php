<x-app-layout :title="$isAgentsView ? 'Mon Équipe' : 'Mon Équipe Terrain'">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">{{ $isAgentsView ? 'Mon Équipe' : 'Mon Équipe Terrain' }}</h1>
    </x-slot>

    @php
        $role = auth()->user()->role;
        $canManageTeam = in_array($role, [\App\Enums\Role::ChefMarketing, \App\Enums\Role::Admin, \App\Enums\Role::AgentMarketeur], true);
        $createLabel = $isAgentsView ? 'Créer un Agent' : 'Créer un Marketeur Terrain';
    @endphp

    <div class="space-y-6">
        @if ($canManageTeam)
            <div class="flex justify-end">
                <a href="{{ route('agents.create') }}" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    {{ $createLabel }}
                </a>
            </div>
        @endif

        {{-- Membres de l'équipe --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-base font-semibold text-gray-900">{{ $isAgentsView ? 'Mes Agents Commerciaux' : 'Mes Marketeurs Terrain' }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">{{ $isAgentsView ? 'Agent' : 'Marketeur' }}</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Commandes mois</th>
                            <th class="px-6 py-3">CA mois</th>
                            <th class="px-6 py-3">Statut</th>
                            <th class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($members as $member)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $member['user']->name }}</td>
                                <td class="px-6 py-4 text-[#6366F1]">{{ $member['user']->email }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $member['orders_count'] }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">@money((float) $member['ca'])</td>
                                <td class="px-6 py-4">
                                    @if ($member['user']->is_active)
                                        <span class="badge badge-green">Actif</span>
                                    @else
                                        <span class="badge badge-orange">Congé</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('agents.show', $member['user']) }}" class="text-sm font-medium text-[#6366F1] hover:underline">Voir</a>
                                        @if ($canManageTeam)
                                            <form method="POST" action="{{ route('agents.toggle-active', $member['user']) }}">
                                                @csrf @method('PATCH')
                                                <button class="text-sm font-medium {{ $member['user']->is_active ? 'text-orange-600' : 'text-green-600' }} hover:underline">
                                                    {{ $member['user']->is_active ? 'Désactiver' : 'Activer' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('agents.destroy', $member['user']) }}"
                                                  onsubmit="return confirm('Supprimer {{ $member['user']->name }} ? Cette action est irréversible.');">
                                                @csrf @method('DELETE')
                                                <button class="text-sm font-medium text-red-600 hover:underline">Supprimer</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Aucun membre dans votre équipe.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Rapports terrain consolidés --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-900">Rapports terrain récents</h2>
                <span class="text-xs text-gray-400">{{ $reports->count() }} rapport(s)</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Marketeur</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Ventes</th>
                            <th class="px-6 py-3">Statut</th>
                            <th class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($reports as $report)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $report->user?->name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $report->date?->format('d/m/Y') }}</td>
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
                                <td class="px-6 py-4">
                                    <a href="{{ route('terrain.show', $report) }}" class="text-sm font-medium text-[#6366F1] hover:underline">Voir</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">Aucun rapport de l'équipe.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
