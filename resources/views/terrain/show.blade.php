<x-app-layout title="Rapport terrain">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Rapport terrain</h1>
    </x-slot>
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Rapport du {{ $report->date?->format('d/m/Y') }}</h1>
                <p class="text-sm text-gray-500">Par {{ $report->user?->name }} · N+1 : {{ $report->supervisor?->name ?? '—' }}</p>
            </div>
            <a href="{{ url()->previous() }}" class="btn-secondary">Retour</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Unités vendues</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $report->nb_ventes }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Prix total</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">@money((float) $report->montant_total)</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Date d'envoi</p>
                <p class="mt-1 text-gray-900">{{ $report->created_at?->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Ventes regroupées par client / magasin --}}
        @php($groupes = $report->items->groupBy(fn ($item) => $item->client?->name ?? 'Non précisé'))

        @forelse ($groupes as $clientNom => $items)
            @php($sousTotalGroupe = $items->sum('sous_total'))
            <div class="card p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">{{ $clientNom }}</h2>
                    <span class="text-sm font-semibold text-gray-700">@money((float) $sousTotalGroupe)</span>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Produit</th>
                            <th class="px-6 py-3">Quantité</th>
                            <th class="px-6 py-3">Prix unitaire</th>
                            <th class="px-6 py-3 text-right">Prix global</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($items as $item)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $item->product?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $item->quantite }}</td>
                                <td class="px-6 py-4 text-gray-700">@money((float) $item->prix_unitaire)</td>
                                <td class="px-6 py-4 text-right font-medium text-gray-900">@money((float) $item->sous_total)</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <div class="card text-center text-gray-400 py-8">Aucun produit déclaré.</div>
        @endforelse

        <div class="card flex items-center justify-between">
            <span class="text-sm font-semibold text-gray-700">Total général</span>
            <span class="text-lg font-bold text-gray-900">@money((float) $report->montant_total)</span>
        </div>
    </div>
</x-app-layout>
