<x-app-layout title="Nouvelle facture">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Nouvelle facture</h1>
    </x-slot>

    @php
        $productsJson = $products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'price' => (float) $p->price])->values();
    @endphp

    <div class="max-w-2xl mx-auto space-y-6" x-data="invoiceForm({{ $productsJson->toJson() }})">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Établir une facture pour un de vos clients.</p>
            <a href="{{ route('invoices.index') }}" class="btn-secondary">Retour</a>
        </div>

        @if ($clients->isEmpty())
            <div class="card text-center py-12 text-gray-400">Aucun client rattaché. Créez d'abord un client.</div>
        @else
            <form method="POST" action="{{ route('invoices.store') }}" class="card space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label" for="client_id">Client</label>
                        <select id="client_id" name="client_id" class="form-input" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label" for="type_vente">Type de vente</label>
                        <select id="type_vente" name="type_vente" class="form-input" required>
                            @foreach (\App\Enums\SaleType::cases() as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="form-label !mb-0">Produits</label>
                        <button type="button" @click="addLine" class="btn-secondary !py-1.5 !px-3 text-xs">+ Ajouter</button>
                    </div>
                    <div class="space-y-3">
                        <template x-for="(line, index) in lines" :key="index">
                            <div class="grid grid-cols-12 gap-2 items-end rounded-lg border border-gray-200 p-3">
                                <div class="col-span-7">
                                    <label class="text-xs text-gray-500">Produit</label>
                                    <select class="form-input !py-2" :name="`items[${index}][product_id]`" x-model.number="line.product_id" @change="onProduct(index)" required>
                                        <option value="">Sélectionner</option>
                                        <template x-for="p in products" :key="p.id">
                                            <option :value="p.id" x-text="p.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <label class="text-xs text-gray-500">Qté</label>
                                    <input type="number" min="1" class="form-input !py-2" :name="`items[${index}][quantite]`" x-model.number="line.quantite" required>
                                </div>
                                <div class="col-span-2 text-right">
                                    <button type="button" @click="removeLine(index)" class="text-red-500 hover:text-red-700 text-sm" x-show="lines.length > 1">Suppr.</button>
                                </div>
                                <div class="col-span-12 text-right text-xs text-gray-500">
                                    Sous-total : <span class="font-medium text-gray-900" x-text="lineTotal(line).toFixed(2) + ' $'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-3">
                        <span class="text-sm text-gray-500">Total</span>
                        <span class="text-lg font-bold text-gray-900" x-text="total().toFixed(2) + ' $'"></span>
                    </div>
                </div>

                <div>
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="2" class="form-input"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn-primary">Créer la facture</button>
                </div>
            </form>
        @endif
    </div>

    <script>
        function invoiceForm(products) {
            return {
                products,
                lines: [{ product_id: '', quantite: 1, price: 0 }],
                addLine() { this.lines.push({ product_id: '', quantite: 1, price: 0 }); },
                removeLine(i) { this.lines.splice(i, 1); },
                onProduct(i) {
                    const p = this.products.find(p => p.id === this.lines[i].product_id);
                    this.lines[i].price = p ? p.price : 0;
                },
                lineTotal(line) { return (line.price || 0) * (line.quantite || 0); },
                total() { return this.lines.reduce((s, l) => s + this.lineTotal(l), 0); },
            };
        }
    </script>
</x-app-layout>
