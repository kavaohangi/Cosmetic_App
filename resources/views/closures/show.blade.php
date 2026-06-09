<x-app-layout title="Rapport de clôture">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Rapport de clôture</h1>
    </x-slot>

    @php($p = $closure->payload ?? [])

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">{{ $closure->user?->name }} — {{ $closure->role?->label() }}</p>
                <p class="text-xl font-bold text-gray-900">Journée du {{ $closure->day?->date?->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('closures.index') }}" class="btn-secondary">Retour</a>
        </div>

        @if (in_array($p['type'] ?? '', ['chef_marketing', 'agent_marketeur']))
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="card"><p class="text-xs uppercase text-gray-400 font-medium">Ventes comptant</p><p class="mt-1 text-xl font-bold text-green-600">@money((float) $closure->ventes_comptant)</p></div>
                <div class="card"><p class="text-xs uppercase text-gray-400 font-medium">Ventes crédit</p><p class="mt-1 text-xl font-bold text-orange-600">@money((float) $closure->ventes_credit)</p></div>
                <div class="card"><p class="text-xs uppercase text-gray-400 font-medium">Livraisons</p><p class="mt-1 text-xl font-bold text-gray-900">{{ $p['nb_livraisons'] ?? 0 }}</p></div>
            </div>

            <div class="card p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Détail des livraisons</h2></div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr><th class="px-6 py-3">Référence</th><th class="px-6 py-3">Client</th><th class="px-6 py-3">Type</th><th class="px-6 py-3 text-right">Montant</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($p['livraisons'] ?? [] as $l)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $l['reference'] }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $l['client'] ?? '—' }}</td>
                                <td class="px-6 py-4"><span class="badge {{ ($l['type'] ?? '') === 'credit' ? 'badge-orange' : 'badge-green' }}">{{ ucfirst($l['type'] ?? '') }}</span></td>
                                <td class="px-6 py-4 text-right text-gray-900">@money((float) ($l['montant'] ?? 0))</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Aucune livraison ce jour.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @elseif (($p['type'] ?? '') === 'magasinier')
            @php($sections = [
                'produits_ajoutes' => 'Produits ajoutés',
                'produits_ajustes' => 'Produits ajustés',
                'produits_sortis' => 'Produits sortis (bon de sortie)',
            ])
            @foreach ($sections as $key => $label)
                <div class="card p-0 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">{{ $label }}</h2></div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($p[$key] ?? [] as $row)
                                <tr><td class="px-6 py-3 font-medium text-gray-900">{{ $row['product'] ?? '—' }}</td><td class="px-6 py-3 text-right text-gray-700">{{ abs($row['quantite'] ?? 0) }}</td></tr>
                            @empty
                                <tr><td class="px-6 py-3 text-gray-400">Aucun.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="card">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Produits en rupture</h3>
                    <ul class="text-sm text-gray-600 list-disc list-inside">
                        @forelse ($p['produits_en_rupture'] ?? [] as $name)<li>{{ $name }}</li>@empty<li class="text-gray-400 list-none">Aucun</li>@endforelse
                    </ul>
                </div>
                <div class="card">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">En rupture <span class="text-red-600">mais commandés</span></h3>
                    <ul class="text-sm text-gray-600 list-disc list-inside">
                        @forelse ($p['produits_en_rupture_commandes'] ?? [] as $name)<li>{{ $name }}</li>@empty<li class="text-gray-400 list-none">Aucun</li>@endforelse
                    </ul>
                </div>
            </div>
        @elseif (($p['type'] ?? '') === 'marketeur_terrain')
            <div class="card">
                <p class="text-sm text-gray-500">Magasin : <span class="font-medium text-gray-900">{{ $p['magasin'] ?? '—' }}</span> · Rapports : {{ $p['nb_rapports'] ?? 0 }}</p>
                <p class="mt-1 text-lg font-bold text-gray-900">Total : @money((float) ($p['total'] ?? 0))</p>
            </div>
            <div class="card p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Produits vendus</h2></div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr><th class="px-6 py-3">Produit</th><th class="px-6 py-3">Quantité</th><th class="px-6 py-3 text-right">Montant</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($p['produits'] ?? [] as $row)
                            <tr><td class="px-6 py-3 font-medium text-gray-900">{{ $row['product'] ?? '—' }}</td><td class="px-6 py-3 text-gray-700">{{ $row['quantite'] ?? 0 }}</td><td class="px-6 py-3 text-right text-gray-900">@money((float) ($row['montant'] ?? 0))</td></tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400">Aucune vente.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="card text-sm text-gray-500">Rapport généré le {{ $closure->closed_at?->format('d/m/Y H:i') }}.</div>
        @endif
    </div>
</x-app-layout>
