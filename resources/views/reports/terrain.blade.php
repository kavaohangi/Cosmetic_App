<x-app-layout title="Rapports d'équipe">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Rapports d'équipe terrain</h1>
    </x-slot>

@php
    $qs = request()->query();
@endphp

    <div class="space-y-6">
        {{-- Filtres --}}
        <form method="GET" action="{{ route('reports.terrain') }}" class="card">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Période</label>
                    <select name="period" class="form-input w-full" onchange="this.form.submit()">
                        <option value="jour" @selected($period === 'jour')>Aujourd'hui</option>
                        <option value="semaine" @selected($period === 'semaine')>Cette semaine</option>
                        <option value="mois" @selected($period === 'mois')>Ce mois</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Agent terrain</label>
                    <select name="agent_id" class="form-input w-full">
                        <option value="">Tous</option>
                        @foreach ($agents as $a)
                            <option value="{{ $a->id }}" @selected(($filters['agent_id'] ?? null) == $a->id)>{{ $a->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Du</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-input w-full">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Au</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-input w-full">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn-primary">Filtrer</button>
                    <a href="{{ route('reports.terrain') }}" class="btn-secondary">Reset</a>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">Période analysée : {{ $from->format('d/m/Y') }} → {{ $to->format('d/m/Y') }}</p>
        </form>

        {{-- KPIs + exports --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 flex-1 min-w-[260px]">
                <div class="card">
                    <p class="text-xs uppercase text-gray-400 font-medium">CA total</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ number_format($totalCa, 0, ',', ' ') }} FCFA</p>
                </div>
                <div class="card">
                    <p class="text-xs uppercase text-gray-400 font-medium">Unités vendues</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ $totalUnites }}</p>
                </div>
                <div class="card">
                    <p class="text-xs uppercase text-gray-400 font-medium">Rapports</p>
                    <p class="mt-1 text-xl font-bold text-gray-900">{{ $reports->count() }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('reports.terrain.pdf', $qs) }}" class="btn-secondary">Exporter PDF</a>
                <a href="{{ route('reports.terrain.excel', $qs) }}" class="btn-secondary">Exporter Excel</a>
                <form method="POST" action="{{ route('reports.terrain.submit') }}">
                    @csrf
                    @foreach ($qs as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <button class="btn-primary">Soumettre au Chef Marketing</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Classement agents --}}
            <div class="card p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Classement des agents</h2></div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">#</th>
                            <th class="px-6 py-3">Agent</th>
                            <th class="px-6 py-3">Magasin</th>
                            <th class="px-6 py-3">Unités</th>
                            <th class="px-6 py-3 text-right">Prix total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($leaderboard as $i => $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-bold text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $row['user']?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $row['magasin'] ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $row['unites'] }}</td>
                                <td class="px-6 py-4 text-right font-medium text-gray-900">{{ number_format($row['ca'], 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Aucune donnée sur la période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Top produits --}}
            <div class="card p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Produits les plus vendus</h2></div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Produit</th>
                            <th class="px-6 py-3">Quantité</th>
                            <th class="px-6 py-3 text-right">CA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($topProducts as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $p['name'] }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $p['quantite'] }}</td>
                                <td class="px-6 py-4 text-right font-medium text-gray-900">{{ number_format($p['ca'], 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">Aucune vente sur la période.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Détail des rapports --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Détail des rapports</h2></div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Agent</th>
                        <th class="px-6 py-3">Unités</th>
                        <th class="px-6 py-3">CA</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($reports as $report)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-gray-500">{{ $report->date?->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $report->user?->name }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $report->items->sum('quantite') }}</td>
                            <td class="px-6 py-4 text-gray-900">{{ number_format($report->montant_total, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('terrain.show', $report) }}" class="text-sm font-medium text-[#6366F1] hover:underline">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">Aucun rapport sur la période.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
