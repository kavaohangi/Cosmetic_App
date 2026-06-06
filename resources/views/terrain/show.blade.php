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

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-5">
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Unités vendues</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $report->nb_ventes }}</p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Chiffre d'affaires</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ number_format($report->montant_total, 0, ',', ' ') }} <span class="text-sm font-medium text-gray-500">FCFA</span></p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Rupture stock</p>
                <p class="mt-2">
                    @if ($report->rupture_stock)
                        <span class="badge badge-red">Oui</span>
                    @else
                        <span class="badge badge-green">Non</span>
                    @endif
                </p>
            </div>
            <div class="card">
                <p class="text-xs uppercase text-gray-400 font-medium">Date d'envoi</p>
                <p class="mt-1 text-gray-900">{{ $report->created_at?->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Produits vendus --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Produits vendus</h2></div>
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
                    @forelse ($report->items as $item)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $item->product?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $item->quantite }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ number_format((float) $item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900">{{ number_format((float) $item->sous_total, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-400">Aucun produit déclaré.</td></tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Total</td>
                        <td class="px-6 py-3 text-right text-base font-bold text-gray-900">{{ number_format($report->montant_total, 0, ',', ' ') }} FCFA</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="card space-y-4">
            <div>
                <p class="text-xs uppercase text-gray-400 font-medium">Plaintes clients</p>
                <p class="mt-1 text-gray-700">{{ $report->plaintes_clients ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-400 font-medium">Propositions clients</p>
                <p class="mt-1 text-gray-700">{{ $report->propositions_clients ?? '—' }}</p>
            </div>
            @if ($report->photo_url)
                <div>
                    <p class="text-xs uppercase text-gray-400 font-medium">Photo rayonnage</p>
                    <img src="{{ \Illuminate\Support\Str::startsWith($report->photo_url, 'http') ? $report->photo_url : \Illuminate\Support\Facades\Storage::url($report->photo_url) }}"
                         alt="Photo rayonnage" class="mt-2 rounded-lg border border-gray-200 max-h-80">
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
