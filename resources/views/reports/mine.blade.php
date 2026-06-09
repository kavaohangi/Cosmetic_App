<x-app-layout title="Mon évaluation">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Mon évaluation</h1>
    </x-slot>

    <div class="space-y-6">
        {{-- Filtre période --}}
        <form method="GET" action="{{ route('reports.mine') }}" class="card">
            <div class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Période</label>
                    <select name="period" class="form-input" onchange="this.form.submit()">
                        <option value="jour" @selected($period === 'jour')>Aujourd'hui</option>
                        <option value="semaine" @selected($period === 'semaine')>Cette semaine</option>
                        <option value="mois" @selected($period === 'mois')>Ce mois</option>
                    </select>
                </div>
                <p class="text-xs text-gray-400">{{ $from->format('d/m/Y') }} → {{ $to->format('d/m/Y') }}</p>
            </div>
        </form>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Prix total des ventes</p>
                <p class="mt-1 text-2xl font-bold text-indigo-600">{{ number_format($totalCa, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Unités vendues</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $totalUnites }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Rapports soumis</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $reports->count() }}</p>
            </div>
        </div>

        {{-- Marchandises vendues --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Marchandises vendues</h2></div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Marchandise</th>
                        <th class="px-6 py-3">Quantité vendue</th>
                        <th class="px-6 py-3 text-right">Prix total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($products as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $p['name'] }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $p['quantite'] }}</td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900">{{ number_format($p['ca'], 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-10 text-center text-gray-400">Aucune vente sur cette période.</td></tr>
                    @endforelse
                </tbody>
                @if ($products->isNotEmpty())
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td class="px-6 py-3 text-right text-sm font-semibold text-gray-700" colspan="2">Total</td>
                            <td class="px-6 py-3 text-right text-base font-bold text-gray-900">{{ number_format($totalCa, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-app-layout>
