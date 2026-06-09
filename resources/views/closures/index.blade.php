<x-app-layout title="Clôture journalière">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Clôture journalière</h1>
    </x-slot>

    <div class="space-y-6">
        {{-- Carte du jour --}}
        <div class="card">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Journée du</p>
                    <p class="text-xl font-bold text-gray-900">{{ $today->format('d/m/Y') }}</p>
                </div>
                <div>
                    @if (! $isWorkingDay)
                        <span class="badge badge-gray">Dimanche — jour non ouvrable</span>
                    @elseif ($alreadyClosed)
                        <span class="badge badge-green">Journée déjà clôturée</span>
                    @else
                        <form method="POST" action="{{ route('closures.store') }}">
                            @csrf
                            <button type="submit" class="btn-primary">Clôturer la journée</button>
                        </form>
                    @endif
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">La clôture génère automatiquement votre rapport du jour.</p>
        </div>

        {{-- Historique --}}
        <div class="card p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Historique des clôtures</h2></div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Ventes comptant</th>
                        <th class="px-6 py-3">Ventes crédit</th>
                        <th class="px-6 py-3">Clôturée le</th>
                        <th class="px-6 py-3 text-right">Rapport</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($closures as $closure)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $closure->day?->date?->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-gray-700">@money((float) $closure->ventes_comptant)</td>
                            <td class="px-6 py-4 text-gray-700">@money((float) $closure->ventes_credit)</td>
                            <td class="px-6 py-4 text-gray-500">{{ $closure->closed_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('closures.show', $closure) }}" class="text-sm font-medium text-[#6366F1] hover:underline">Voir</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">Aucune clôture enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $closures->links() }}</div>
    </div>
</x-app-layout>
