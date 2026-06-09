<x-app-layout title="Taux de change">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Taux de change USD → FC</h1>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="card">
            <p class="text-sm text-gray-500">Taux actif</p>
            @if ($current)
                <p class="mt-1 text-3xl font-bold text-gray-900">1 $ = {{ number_format((float) $current->taux_fc, 2, ',', ' ') }} FC</p>
                <p class="mt-1 text-xs text-gray-400">Défini par {{ $current->definer?->name ?? '—' }} le {{ $current->created_at?->format('d/m/Y H:i') }}</p>
            @else
                <p class="mt-1 text-lg font-semibold text-orange-600">Aucun taux défini. Les prix s'affichent en USD uniquement.</p>
            @endif
        </div>

        <form method="POST" action="{{ route('conversion-rates.store') }}" class="card flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="form-label" for="taux_fc">Nouveau taux (1 USD = ? FC)</label>
                <input id="taux_fc" type="number" step="0.0001" min="0.0001" name="taux_fc" value="{{ old('taux_fc', $current?->taux_fc) }}" class="form-input" required>
                @error('taux_fc')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn-primary">Mettre à jour</button>
        </form>

        <div class="card p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200"><h2 class="text-base font-semibold text-gray-900">Historique</h2></div>
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr><th class="px-6 py-3">Taux</th><th class="px-6 py-3">Défini par</th><th class="px-6 py-3">Date</th><th class="px-6 py-3">État</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($history as $rate)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ number_format((float) $rate->taux_fc, 2, ',', ' ') }} FC</td>
                            <td class="px-6 py-3 text-gray-700">{{ $rate->definer?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $rate->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-3">@if ($rate->actif)<span class="badge badge-green">Actif</span>@else<span class="badge badge-gray">Archivé</span>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
