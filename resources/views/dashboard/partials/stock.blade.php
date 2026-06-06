<div class="space-y-6">
    <div class="flex items-center justify-end">
        <a href="{{ route('products.index') }}" class="btn-primary">Ajuster Stock</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @include('partials.kpi', [
            'label' => 'Produits référencés',
            'value' => $kpis['total_produits'],
            'tone' => 'indigo',
            'hint' => 'Catalogue actif',
            'icon' => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>',
        ])
        @include('partials.kpi', [
            'label' => 'Valeur du stock',
            'value' => number_format($kpis['valeur_stock'], 0, ',', ' ').' FCFA',
            'tone' => 'green',
            'hint' => 'Valorisation totale',
            'hintTone' => 'green',
            'icon' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
        ])
        @include('partials.kpi', [
            'label' => 'Produits en rupture',
            'value' => $kpis['en_rupture'],
            'tone' => 'red',
            'valueTone' => 'red',
            'hint' => 'Réapprovisionnement requis',
            'icon' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        ])
    </div>

    <div class="rounded-xl border border-red-200 bg-red-50 overflow-hidden">
        <div class="px-6 py-4 border-b border-red-200 flex items-center gap-2">
            <svg class="text-red-600" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <h2 class="text-base font-semibold text-red-800">Alertes de rupture</h2>
        </div>
        <div class="divide-y divide-red-100">
            @forelse ($alertes as $product)
                <div class="flex items-center justify-between px-6 py-4 bg-white/60">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                        <p class="text-xs text-gray-500">SKU {{ $product->sku }} · seuil {{ $product->seuil_alerte }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="badge badge-red">Stock : {{ $product->stock }}</span>
                        <a href="{{ route('products.edit', $product) }}" class="btn-secondary !py-1.5 !px-3 text-xs">Ajuster Stock</a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-sm text-gray-500 bg-white/60">Aucune rupture de stock. 🎉</div>
            @endforelse
        </div>
    </div>
</div>
