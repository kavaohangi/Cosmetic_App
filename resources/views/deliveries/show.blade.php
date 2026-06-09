<x-app-layout title="Bon de livraison">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Bon de livraison {{ $delivery->reference }}</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Client : {{ $delivery->client?->name ?? '—' }} · Agent : {{ $delivery->agent?->name ?? '—' }}</p>
                <p class="text-sm text-gray-500">Date : {{ $delivery->date?->format('d/m/Y') }} · Type : {{ $delivery->type_vente->label() }}</p>
            </div>
            <a href="{{ route('deliveries.index') }}" class="btn-secondary">Retour</a>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Statut</span>
                @if ($delivery->statut === \App\Enums\DeliveryStatus::Livre)
                    <span class="badge badge-green">{{ $delivery->statut->label() }}</span>
                @else
                    <span class="badge badge-gray">{{ $delivery->statut->label() }}</span>
                @endif
            </div>
            @if ($delivery->delivered_at)
                <p class="mt-2 text-xs text-gray-400">Livré le {{ $delivery->delivered_at->format('d/m/Y H:i') }} par {{ $delivery->deliverer?->name }}</p>
            @endif
        </div>

        @if ($delivery->statut === \App\Enums\DeliveryStatus::Livre)
            {{-- Lignes en lecture seule --}}
            <div class="card p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Produits livrés</h2></div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-6 py-3">Produit</th>
                            <th class="px-6 py-3">Livrée</th>
                            <th class="px-6 py-3">Rendue</th>
                            <th class="px-6 py-3">Nette</th>
                            <th class="px-6 py-3 text-right">Montant</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($delivery->lines as $line)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $line->product?->name }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $line->quantite }}</td>
                                <td class="px-6 py-4 text-gray-700">{{ $line->quantite_rendue }}</td>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $line->quantiteNette() }}</td>
                                <td class="px-6 py-4 text-right text-gray-900">@money((float) $line->prix_unitaire * $line->quantiteNette())</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Total net</td>
                            <td class="px-6 py-3 text-right text-base font-bold text-gray-900">@money($delivery->montantTotal())</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($delivery->goodsIssueNote)
                <div class="card">
                    <p class="text-sm font-semibold text-gray-900">Bon de sortie associé : {{ $delivery->goodsIssueNote->reference }}</p>
                    <p class="text-xs text-gray-500 mt-1">Émis le {{ $delivery->goodsIssueNote->date?->format('d/m/Y') }} — justifie la sortie physique du stock.</p>
                </div>
            @endif
        @else
            {{-- Formulaire de confirmation avec quantités rendues --}}
            @can('confirm', \App\Models\Delivery::class)
                <form method="POST" action="{{ route('deliveries.confirm', $delivery) }}" class="card p-0 overflow-hidden">
                    @csrf
                    @method('PATCH')
                    <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Confirmer la livraison</h2></div>
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Produit</th>
                                <th class="px-6 py-3">Quantité livrée</th>
                                <th class="px-6 py-3">Quantité rendue au stock</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($delivery->lines as $line)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $line->product?->name }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ $line->quantite }}</td>
                                    <td class="px-6 py-4">
                                        <input type="number" name="returns[{{ $line->id }}]" min="0" max="{{ $line->quantite }}" value="0" class="form-input w-28">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                        <button type="submit" class="btn-primary">Confirmer & générer le bon de sortie</button>
                    </div>
                </form>
                <p class="text-xs text-gray-400">La confirmation déduit le stock physique (quantité livrée − quantité rendue) et génère un bon de sortie.</p>
            @else
                <div class="card text-sm text-gray-500">En attente de confirmation par le magasinier ou le chef marketing.</div>
            @endcan
        @endif
    </div>
</x-app-layout>
