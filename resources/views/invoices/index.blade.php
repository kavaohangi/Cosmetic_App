<x-app-layout title="Factures">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Factures</h1>
    </x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <form method="GET" action="{{ route('invoices.index') }}" class="flex items-end gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
                    <select name="statut" class="form-input" onchange="this.form.submit()">
                        <option value="">Tous</option>
                        @foreach ($statuts as $s)
                            <option value="{{ $s->value }}" @selected(request('statut') === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            @can('create', \App\Models\Invoice::class)
                <a href="{{ route('invoices.create') }}" class="btn-primary">+ Nouvelle facture</a>
            @endcan
        </div>

        <div class="card p-0 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Référence</th>
                        <th class="px-6 py-3">Client</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Montant</th>
                        <th class="px-6 py-3">Statut</th>
                        <th class="px-6 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $invoice->reference }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $invoice->client?->name ?? '—' }}</td>
                            <td class="px-6 py-4"><span class="badge {{ $invoice->type_vente === \App\Enums\SaleType::Credit ? 'badge-orange' : 'badge-green' }}">{{ $invoice->type_vente->label() }}</span></td>
                            <td class="px-6 py-4 text-gray-900">@money((float) $invoice->montant)</td>
                            <td class="px-6 py-4">
                                @php($st = $invoice->statut)
                                <span class="badge {{ $st === \App\Enums\InvoiceStatus::Payee ? 'badge-green' : ($st === \App\Enums\InvoiceStatus::Annulee ? 'badge-red' : ($st === \App\Enums\InvoiceStatus::Partielle ? 'badge-orange' : 'badge-gray')) }}">{{ $st->label() }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-sm font-medium text-[#6366F1] hover:underline">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Aucune facture.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $invoices->links() }}</div>
    </div>
</x-app-layout>
