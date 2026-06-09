<x-app-layout title="Facture">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Facture {{ $invoice->reference }}</h1>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Client : {{ $invoice->client?->name }} · Date : {{ $invoice->date?->format('d/m/Y') }}</p>
                <p class="text-sm text-gray-500">Type : {{ $invoice->type_vente->label() }}</p>
            </div>
            <a href="{{ route('invoices.index') }}" class="btn-secondary">Retour</a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="card"><p class="text-xs uppercase text-gray-400 font-medium">Montant</p><p class="mt-1 text-xl font-bold text-gray-900">@money((float) $invoice->montant)</p></div>
            <div class="card"><p class="text-xs uppercase text-gray-400 font-medium">Payé</p><p class="mt-1 text-xl font-bold text-green-600">@money((float) $invoice->montant_paye)</p></div>
            <div class="card"><p class="text-xs uppercase text-gray-400 font-medium">Reste à payer</p><p class="mt-1 text-xl font-bold text-orange-600">@money($invoice->resteAPayer())</p></div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-500">Statut</span>
                @php($st = $invoice->statut)
                <span class="badge {{ $st === \App\Enums\InvoiceStatus::Payee ? 'badge-green' : ($st === \App\Enums\InvoiceStatus::Annulee ? 'badge-red' : ($st === \App\Enums\InvoiceStatus::Partielle ? 'badge-orange' : 'badge-gray')) }}">{{ $st->label() }}</span>
            </div>
        </div>

        <div class="card p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Lignes</h2></div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr><th class="px-6 py-3">Produit</th><th class="px-6 py-3">Qté</th><th class="px-6 py-3">PU</th><th class="px-6 py-3 text-right">Sous-total</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($invoice->lines as $line)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $line->product?->name }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $line->quantite }}</td>
                            <td class="px-6 py-4 text-gray-700">@money((float) $line->prix_unitaire)</td>
                            <td class="px-6 py-4 text-right text-gray-900">@money((float) $line->sous_total)</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr><td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Total</td><td class="px-6 py-3 text-right text-base font-bold text-gray-900">@money((float) $invoice->montant)</td></tr>
                </tfoot>
            </table>
        </div>

        @can('update', $invoice)
            @if ($invoice->statut !== \App\Enums\InvoiceStatus::Payee && $invoice->statut !== \App\Enums\InvoiceStatus::Annulee)
                <form method="POST" action="{{ route('invoices.pay', $invoice) }}" class="card flex items-end gap-3">
                    @csrf
                    @method('PATCH')
                    <div class="flex-1">
                        <label class="form-label" for="montant">Enregistrer un paiement ($)</label>
                        <input id="montant" type="number" step="0.01" min="0.01" max="{{ $invoice->resteAPayer() }}" name="montant" class="form-input" required>
                    </div>
                    <button type="submit" class="btn-primary">Encaisser</button>
                </form>
            @endif
        @endcan
    </div>
</x-app-layout>
