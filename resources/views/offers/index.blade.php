<x-app-layout title="Offres">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Offres & promotions</h1>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500">Gérez les offres visibles par les clients.</p>
            <a href="{{ route('offers.create') }}" class="btn-primary">Nouvelle offre</a>
        </div>

        <div class="card p-0 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Titre</th>
                        <th class="px-6 py-3">Produit</th>
                        <th class="px-6 py-3">Remise</th>
                        <th class="px-6 py-3">Période</th>
                        <th class="px-6 py-3">Statut</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($offers as $offer)
                        <tr>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $offer->titre }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $offer->product?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $offer->remise_pourcentage ? rtrim(rtrim(number_format((float) $offer->remise_pourcentage, 2), '0'), '.').'%' : '—' }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $offer->date_debut?->format('d/m/Y') ?? '—' }} → {{ $offer->date_fin?->format('d/m/Y') ?? '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $offer->actif ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $offer->actif ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form method="POST" action="{{ route('offers.destroy', $offer) }}" onsubmit="return confirm('Supprimer cette offre ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Aucune offre.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $offers->links() }}
    </div>
</x-app-layout>
