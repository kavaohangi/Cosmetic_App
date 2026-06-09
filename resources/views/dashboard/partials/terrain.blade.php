@php
    $authUser = auth()->user();
    $supervisorName = $supervisor?->name ?? 'N+1';
@endphp

<div class="space-y-8">
    {{-- Section 1 : Accueil --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Bonjour {{ $authUser->name }} 👋</h1>
        <p class="text-sm text-gray-500">Votre espace agent terrain du {{ now()->format('d/m/Y') }}.</p>
    </div>

    {{-- Section 2 : KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card">
            <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">Ventes Aujourd'hui</p>
            <p class="text-3xl font-bold text-indigo-600 mt-2">{{ number_format($kpis['today_sales'], 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-gray-500 mt-1">Montant total</p>
        </div>

        <div class="card">
            <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">Ventes Cette Semaine</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($kpis['week_sales'], 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-gray-500 mt-1">Du lundi au dimanche</p>
        </div>

        <div class="card">
            <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">Ventes Ce Mois</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($kpis['month_sales'], 0, ',', ' ') }} FCFA</p>
            <p class="text-xs text-gray-500 mt-1">Montant cumulé</p>
        </div>

        <div class="card">
            <p class="text-sm text-gray-600 uppercase tracking-wide font-semibold">Plaintes en Attente</p>
            <p class="text-3xl font-bold text-orange-600 mt-2">{{ $kpis['pending_complaints'] }}</p>
            <p class="text-xs text-gray-500 mt-1">À examiner par votre manager</p>
        </div>
    </div>

    {{-- Section 3 : Raccourcis --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <a href="{{ route('products.index') }}" class="card hover:shadow-md hover:border-indigo-400 transition-all group">
            <div class="h-11 w-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/></svg>
            </div>
            <p class="text-base font-semibold text-gray-900 group-hover:text-indigo-600">Catalogue</p>
            <p class="text-sm text-gray-500">Voir les produits disponibles.</p>
        </a>

        <a href="{{ route('terrain.index') }}" class="card hover:shadow-md hover:border-indigo-400 transition-all group">
            <div class="h-11 w-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v5h5"/><path d="M10 12H8"/><path d="M16 12h-2"/><path d="M10 16H8"/><path d="M16 16h-2"/></svg>
            </div>
            <p class="text-base font-semibold text-gray-900 group-hover:text-indigo-600">Mes Rapports</p>
            <p class="text-sm text-gray-500">Historique de vos rapports.</p>
        </a>

        <a href="{{ route('terrain-complaints.index') }}" class="card hover:shadow-md hover:border-indigo-400 transition-all group">
            <div class="h-11 w-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <p class="text-base font-semibold text-gray-900 group-hover:text-indigo-600">Plaintes & Propositions</p>
            <p class="text-sm text-gray-500">Signaler des problèmes ou suggestions.</p>
        </a>

        <a href="{{ route('messages.index') }}" class="card hover:shadow-md hover:border-indigo-400 transition-all group">
            <div class="h-11 w-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <p class="text-base font-semibold text-gray-900 group-hover:text-indigo-600">Messages</p>
            <p class="text-sm text-gray-500">Échanger avec votre manager.</p>
        </a>
    </div>

    {{-- Section 4 : Formulaire rapport --}}
    <div class="card">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-lg font-bold text-gray-900">Rapport du {{ now()->format('d/m/Y') }}</h2>
            @if ($todayReport)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    ✓ Déjà soumis aujourd'hui
                </span>
            @endif
        </div>

        <form method="POST" action="{{ route('terrain.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="date" value="{{ now()->toDateString() }}">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Marchandises vendues</label>
                <p class="text-xs text-gray-500 mb-3">Indiquez chaque produit vendu, la quantité et le prix unitaire.</p>
                <div id="items-container" class="space-y-3">
                    <div class="item-row flex flex-col gap-2 md:flex-row md:gap-2">
                        <select name="items[0][product_id]" required class="flex-1 form-input">
                            <option value="">-- Sélectionner produit --</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="items[0][quantite]" placeholder="Qté" required class="w-20 form-input">
                        <input type="number" step="0.01" name="items[0][prix_unitaire]" placeholder="Prix unitaire" required class="w-32 form-input">
                        <button type="button" class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg remove-item">
                            Supprimer
                        </button>
                    </div>
                </div>
                <button type="button" id="add-item" class="mt-3 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg">
                    + Ajouter un produit
                </button>
            </div>

            <div class="pt-2 border-t border-gray-200">
                <button type="submit" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                    Envoyer à {{ $supervisorName }}
                </button>
            </div>
        </form>
    </div>

    {{-- Section 5 : Mon équipe terrain --}}
    <div class="card">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Mon équipe terrain</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @forelse ($colleagues as $colleague)
                <a href="{{ route('messages.conversation', $colleague) }}"
                   class="flex items-center gap-3 rounded-lg border border-gray-200 px-4 py-3 hover:border-indigo-400 hover:bg-gray-50 transition-all">
                    <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center text-sm font-semibold text-white">
                        {{ \Illuminate\Support\Str::of($colleague->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $colleague->name }}</p>
                        <p class="text-xs text-gray-500">Collègue terrain</p>
                    </div>
                    <svg class="ml-auto text-gray-300" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
                </a>
            @empty
                <p class="text-sm text-gray-400">Aucun collègue rattaché au même superviseur.</p>
            @endforelse
        </div>
    </div>
</div>

<script>
    let itemCount = 1;

    document.getElementById('add-item')?.addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const newItem = document.createElement('div');
        newItem.classList.add('item-row', 'flex', 'flex-col', 'gap-2', 'md:flex-row', 'md:gap-2');
        newItem.innerHTML = `
            <select name="items[${itemCount}][product_id]" required class="flex-1 form-input">
                <option value="">-- Sélectionner produit --</option>
                @foreach ($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
            <input type="number" name="items[${itemCount}][quantite]" placeholder="Qté" required class="w-20 form-input">
            <input type="number" step="0.01" name="items[${itemCount}][prix_unitaire]" placeholder="Prix unitaire" required class="w-32 form-input">
            <button type="button" class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg remove-item">
                Supprimer
            </button>
        `;
        container.appendChild(newItem);
        itemCount++;

        // Attach remove listeners
        newItem.querySelector('.remove-item').addEventListener('click', function(e) {
            e.preventDefault();
            newItem.remove();
        });
    });

    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            this.parentElement.remove();
        });
    });
</script>

