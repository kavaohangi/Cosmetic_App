<x-app-layout title="Produits de nouveau disponibles">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Produits de nouveau disponibles</h1>
    </x-slot>

    <div class="space-y-6">
        <div class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <div>
                <p class="text-sm font-semibold text-green-800">Réapprovisionnements récents (30 derniers jours)</p>
                <p class="text-xs text-green-700 mt-0.5">Ces produits étaient en rupture et ont été renouvelés par le magasinier. Vous pouvez relancer vos commandes.</p>
            </div>
        </div>

        @if ($renewed->isEmpty())
            <div class="card text-center py-12 text-gray-400">
                Aucun produit renouvelé récemment.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($renewed as $alert)
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $alert->product?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $alert->product?->category }}</p>
                            </div>
                            <span class="badge badge-green shrink-0">Disponible</span>
                        </div>

                        <div class="mt-4 flex items-center justify-between text-sm">
                            <span class="text-gray-500">Stock actuel</span>
                            <span class="font-semibold text-gray-900">{{ $alert->product?->stock }}</span>
                        </div>

                        <div class="mt-2 text-xs text-gray-400">
                            Renouvelé le {{ $alert->resolved_at?->format('d/m/Y') }}
                            @if ($alert->resolver)
                                · par {{ $alert->resolver->name }}
                            @endif
                        </div>

                        <a href="{{ route('orders.create') }}" class="btn-primary w-full mt-4 !py-2 text-sm">Commander</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
