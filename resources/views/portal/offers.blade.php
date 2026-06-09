<x-app-layout title="Offres">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Offres & promotions</h1>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Profitez de nos offres en cours.</p>
            <div class="flex gap-2">
                <a href="{{ route('portal.orders') }}" class="btn-secondary">Mes commandes</a>
                <a href="{{ route('portal.catalogue') }}" class="btn-secondary">Catalogue</a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            @forelse ($offers as $offer)
                <div class="card border-l-4 border-[#6366F1]">
                    <div class="flex items-start justify-between">
                        <h2 class="text-base font-semibold text-gray-900">{{ $offer->titre }}</h2>
                        @if ($offer->remise_pourcentage)
                            <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-bold text-indigo-700">-{{ rtrim(rtrim(number_format((float) $offer->remise_pourcentage, 2), '0'), '.') }}%</span>
                        @endif
                    </div>
                    @if ($offer->product)
                        <p class="mt-1 text-sm text-gray-500">Produit : {{ $offer->product->name }}</p>
                    @endif
                    @if ($offer->description)
                        <p class="mt-2 text-sm text-gray-700">{{ $offer->description }}</p>
                    @endif
                    @if ($offer->date_fin)
                        <p class="mt-3 text-xs text-gray-400">Valable jusqu'au {{ $offer->date_fin->format('d/m/Y') }}</p>
                    @endif
                </div>
            @empty
                <p class="col-span-full text-center text-gray-400 py-10">Aucune offre en cours.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
