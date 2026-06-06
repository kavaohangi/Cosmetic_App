<x-app-layout title="Modifier le client">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Modifier le client</h1>
    </x-slot>
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $client->name }}</h1>
            <a href="{{ route('clients.index') }}" class="btn-secondary">Retour</a>
        </div>

        <form method="POST" action="{{ route('clients.update', $client) }}" class="space-y-6">
            @csrf @method('PUT')
            @include('clients.partials.form', ['client' => $client])
            <div class="flex justify-end gap-3">
                <a href="{{ route('clients.index') }}" class="btn-secondary">Annuler</a>
                <button type="submit" class="btn-primary">Enregistrer</button>
            </div>
        </form>

        @can('delete', $client)
            <form method="POST" action="{{ route('clients.destroy', $client) }}" onsubmit="return confirm('Supprimer ce client ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">Supprimer le client</button>
            </form>
        @endcan
    </div>
</x-app-layout>
