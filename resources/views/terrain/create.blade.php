<x-app-layout title="Nouveau rapport">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Nouveau rapport</h1>
    </x-slot>

@php
    $supervisorName = auth()->user()->supervisor?->name ?? 'N+1';
    $productsJson = $products->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'price' => (float) $p->price])->values();
    $clientsJson = $clients->map(fn ($c) => ['id' => $c->id, 'label' => $c->name.($c->ville ? ' — '.$c->ville : '')])->values();
@endphp

    <div class="max-w-2xl mx-auto space-y-6"
         x-data="terrainForm({{ $productsJson->toJson() }}, {{ $clientsJson->toJson() }})">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Rapport du {{ now()->format('d/m/Y') }}</h1>
            <a href="{{ route('terrain.index') }}" class="btn-secondary">Retour</a>
        </div>

        <form method="POST" action="{{ route('terrain.store') }}" class="card space-y-5">
            @csrf
            <input type="hidden" name="date" value="{{ now()->toDateString() }}">

            {{-- Produits vendus --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="form-label !mb-0">Produits vendus</label>
                    <button type="button" @click="addLine" class="btn-secondary !py-1.5 !px-3 text-xs">+ Ajouter</button>
                </div>
                <div class="space-y-3">
                    <template x-for="(line, index) in lines" :key="index">
                        <div class="grid grid-cols-12 gap-2 items-end rounded-lg border border-gray-200 p-3">
                            <div class="col-span-6">
                                <label class="text-xs text-gray-500">Produit</label>
                                <select class="form-input !py-2" :name="`items[${index}][product_id]`" x-model.number="line.product_id" @change="onProduct(index)" required>
                                    <option value="">Sélectionner</option>
                                    <template x-for="p in products" :key="p.id">
                                        <option :value="p.id" x-text="p.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-span-6">
                                <label class="text-xs text-gray-500">Client / Magasin</label>
                                <select class="form-input !py-2" :name="`items[${index}][client_id]`" x-model.number="line.client_id">
                                    <option value="">— Non précisé —</option>
                                    <template x-for="c in clients" :key="c.id">
                                        <option :value="c.id" x-text="c.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="col-span-5">
                                <label class="text-xs text-gray-500">Qté</label>
                                <input type="number" min="1" class="form-input !py-2" :name="`items[${index}][quantite]`" x-model.number="line.quantite" required>
                            </div>
                            <div class="col-span-6">
                                <label class="text-xs text-gray-500">Prix unit. ($)</label>
                                <input type="number" min="0" step="0.01" class="form-input !py-2" :name="`items[${index}][prix_unitaire]`" x-model.number="line.prix_unitaire" required>
                            </div>
                            <div class="col-span-1 text-right">
                                <button type="button" @click="removeLine(index)" class="text-red-500 hover:text-red-700" x-show="lines.length > 1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                                </button>
                            </div>
                            <div class="col-span-12 text-right text-xs text-gray-500">
                                Sous-total : <span class="font-medium text-gray-900" x-text="'$' + lineTotal(line).toFixed(2)"></span>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="flex items-center justify-end gap-3 mt-3">
                    <span class="text-sm text-gray-500">Total ventes</span>
                    <span class="text-lg font-bold text-gray-900" x-text="'$' + total().toFixed(2)"></span>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-primary">Envoyer à {{ $supervisorName }}</button>
            </div>
        </form>
    </div>

    <script>
        function terrainForm(products, clients) {
            return {
                products,
                clients,
                lines: [{ product_id: '', client_id: '', quantite: 1, prix_unitaire: 0 }],
                addLine() { this.lines.push({ product_id: '', client_id: '', quantite: 1, prix_unitaire: 0 }); },
                removeLine(i) { this.lines.splice(i, 1); },
                onProduct(i) {
                    const p = this.products.find(p => p.id === this.lines[i].product_id);
                    if (p && (!this.lines[i].prix_unitaire || this.lines[i].prix_unitaire === 0)) {
                        this.lines[i].prix_unitaire = p.price;
                    }
                },
                lineTotal(line) { return (line.prix_unitaire || 0) * (line.quantite || 0); },
                total() { return this.lines.reduce((s, l) => s + this.lineTotal(l), 0); },
            };
        }
    </script>
</x-app-layout>
