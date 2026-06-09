<x-app-layout title="Nouvelle commande">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Nouvelle commande</h1>
    </x-slot>

@php
    $productsJson = $products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'price' => (float) $p->price, 'stock' => (int) $p->stock])->values();
@endphp

    <div class="max-w-4xl mx-auto space-y-6"
         x-data="orderForm({{ $productsJson->toJson() }})">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Nouvelle commande</h1>
            <a href="{{ route('orders.index') }}" class="btn-secondary">Retour</a>
        </div>

        @if (session('rupture_confirmation'))
            <div class="rounded-xl border border-orange-300 bg-orange-50 p-5">
                <h2 class="text-base font-semibold text-orange-800">Confirmation requise : produits en rupture</h2>
                <p class="text-sm text-orange-700 mt-1">Les produits suivants sont indisponibles. Vous pouvez soumettre la commande malgré tout : la production sera alertée.</p>
                <ul class="mt-3 text-sm text-orange-800 list-disc list-inside">
                    @foreach (session('rupture_confirmation') as $m)
                        <li>{{ $m['name'] }} — demandé {{ $m['demande'] }}, disponible {{ $m['disponible'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('orders.store') }}" class="space-y-6">
            @csrf

            <div class="card grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="form-label" for="client_id">Client</label>
                    <select id="client_id" name="client_id" class="form-input" required>
                        <option value="">Sélectionner un client</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }} — {{ $client->ville }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label" for="date_commande">Date de commande</label>
                    <input id="date_commande" type="date" name="date_commande" value="{{ old('date_commande', now()->toDateString()) }}" class="form-input">
                </div>
            </div>

            <div class="card p-0 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Lignes de commande</h2>
                    <button type="button" @click="addLine" class="btn-secondary !py-1.5 !px-3 text-xs">+ Ajouter une ligne</button>
                </div>

                <div class="divide-y divide-gray-100">
                    <template x-for="(line, index) in lines" :key="index">
                        <div class="grid grid-cols-12 gap-3 px-6 py-4 items-end">
                            <div class="col-span-6">
                                <label class="form-label">Produit</label>
                                <select class="form-input" :name="`items[${index}][product_id]`" x-model.number="line.product_id" required>
                                    <option value="">Sélectionner</option>
                                    <template x-for="p in products" :key="p.id">
                                        <option :value="p.id" x-text="`${p.name} ($${p.price.toFixed(2)}) — stock: ${p.stock}`"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-span-3">
                                <label class="form-label">Quantité</label>
                                <input type="number" min="1" class="form-input" :name="`items[${index}][quantite]`" x-model.number="line.quantite" required>
                                <p x-show="line.product_id && line.quantite > stockOf(line.product_id)" x-cloak
                                   class="mt-1 text-xs text-orange-600">
                                    Stock insuffisant (<span x-text="stockOf(line.product_id)"></span> dispo) — confirmation requise.
                                </p>
                            </div>
                            <div class="col-span-2">
                                <label class="form-label">Sous-total</label>
                                <p class="py-2.5 text-sm font-medium text-gray-900" x-text="'$' + lineTotal(line).toFixed(2)"></p>
                            </div>
                            <div class="col-span-1 text-right">
                                <button type="button" @click="removeLine(index)" class="text-red-500 hover:text-red-700" x-show="lines.length > 1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-6">
                    <span class="text-sm text-gray-500">Total</span>
                    <span class="text-xl font-bold text-gray-900" x-text="'$' + total().toFixed(2)"></span>
                </div>
            </div>

            <div class="card">
                <label class="form-label" for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="2" class="form-input" placeholder="Informations complémentaires...">{{ old('notes') }}</textarea>
            </div>

            <div class="card flex items-center gap-3">
                <input type="checkbox" id="confirm_rupture" name="confirm_rupture" value="1" class="rounded border-gray-300" @checked(session('rupture_confirmation'))>
                <label for="confirm_rupture" class="text-sm text-gray-700">Soumettre même si certains produits sont en rupture (la production sera alertée).</label>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('orders.index') }}" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">Créer la commande</button>
            </div>
        </form>
    </div>

    <script>
        function orderForm(products) {
            return {
                products,
                lines: [{ product_id: '', quantite: 1 }],
                addLine() { this.lines.push({ product_id: '', quantite: 1 }); },
                removeLine(i) { this.lines.splice(i, 1); },
                priceOf(id) { const p = this.products.find(p => p.id === id); return p ? p.price : 0; },
                stockOf(id) { const p = this.products.find(p => p.id === id); return p ? p.stock : 0; },
                lineTotal(line) { return this.priceOf(line.product_id) * (line.quantite || 0); },
                total() { return this.lines.reduce((s, l) => s + this.lineTotal(l), 0); },
            };
        }
    </script>
</x-app-layout>
