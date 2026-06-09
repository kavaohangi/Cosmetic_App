<x-app-layout title="Mon marketeur">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Mon marketeur</h1>
    </x-slot>

    @php
        $initials = fn ($name) => \Illuminate\Support\Str::of($name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
    @endphp

    <div class="max-w-2xl mx-auto space-y-6">
        @if ($agent)
            <div class="card">
                <div class="flex items-center gap-4">
                    <div class="h-16 w-16 rounded-full bg-[#6366F1] flex items-center justify-center text-xl font-semibold text-white">
                        {{ $initials($agent->name) }}
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900">{{ $agent->name }}</p>
                        <p class="text-sm text-gray-500">{{ $agent->role->label() }}</p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs uppercase text-gray-400 font-medium">E-mail</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $agent->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase text-gray-400 font-medium">Téléphone</p>
                        <p class="mt-1 font-medium text-gray-900">{{ $agent->phone ?? '—' }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('portal.messages') }}" class="btn-primary">Envoyer un message</a>
                </div>
            </div>
        @else
            <div class="card text-center py-12">
                <p class="text-gray-500">Aucun marketeur ne vous est encore assigné.</p>
                <p class="text-sm text-gray-400 mt-1">Contactez l'entreprise pour qu'un marketeur vous soit attribué.</p>
            </div>
        @endif
    </div>
</x-app-layout>
