<x-app-layout title="Nouveau client">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Nouveau client</h1>
    </x-slot>
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Nouveau client</h1>
            <a href="{{ route('clients.index') }}" class="btn-secondary">Retour</a>
        </div>

        <form method="POST" action="{{ route('clients.store') }}" class="space-y-6">
            @csrf
            @include('clients.partials.form')
            <div class="flex justify-end gap-3">
                <a href="{{ route('clients.index') }}" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">Créer</button>
            </div>
        </form>
    </div>
</x-app-layout>
