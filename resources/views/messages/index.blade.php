<x-app-layout title="Messagerie">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Messagerie</h1>
    </x-slot>

@php
    $authUser = auth()->user();
    $isTerrain = $authUser->role === \App\Enums\Role::MarketeurTerrain;

    $initials = fn ($name) => \Illuminate\Support\Str::of($name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');

    // Pour un marketeur terrain : 1er contact = N+1, le reste = collègues.
    $supervisorContact = null;
    $teamContacts = $contacts;

    if ($isTerrain && $authUser->supervisor_id) {
        $supervisorContact = $contacts->firstWhere('id', $authUser->supervisor_id);
        $teamContacts = $contacts->reject(fn ($c) => $c->id === $authUser->supervisor_id)->values();
    }
@endphp

    <div class="card p-0 overflow-hidden" style="height: calc(100vh - 8rem)">
        <div class="grid grid-cols-1 md:grid-cols-3 h-full">
            {{-- Colonne gauche : contacts --}}
            <div class="border-r border-gray-200 flex flex-col {{ $activeContact ? 'hidden md:flex' : 'flex' }}">
                <div class="px-4 py-4 border-b border-gray-200">
                    <h2 class="text-base font-bold text-gray-900">Conversations</h2>
                </div>
                <div class="flex-1 overflow-y-auto">
                    @if ($isTerrain)
                        @if ($supervisorContact)
                            <p class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">Mon N+1</p>
                            @include('messages.partials.contact', ['contact' => $supervisorContact, 'activeContact' => $activeContact, 'initials' => $initials])
                        @endif
                        <p class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">Mon équipe</p>
                    @endif

                    @forelse ($teamContacts as $contact)
                        @include('messages.partials.contact', ['contact' => $contact, 'activeContact' => $activeContact, 'initials' => $initials])
                    @empty
                        <p class="px-4 py-6 text-sm text-gray-400">Aucun contact disponible.</p>
                    @endforelse
                </div>
            </div>

            {{-- Colonne droite : conversation --}}
            <div class="md:col-span-2 flex flex-col {{ $activeContact ? 'flex' : 'hidden md:flex' }}">
                @if ($activeContact)
                    <div class="px-4 py-3 border-b border-gray-200 flex items-center gap-3">
                        <a href="{{ route('messages.index') }}" class="md:hidden text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                        </a>
                        <div class="h-9 w-9 rounded-full bg-[#6366F1] flex items-center justify-center text-sm font-semibold text-white">{{ $initials($activeContact->name) }}</div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $activeContact->name }}</p>
                            <p class="text-xs text-gray-500">{{ $activeContact->role->label() }}</p>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto px-4 py-5 space-y-3 bg-[#F9FAFB]" id="chat-scroll">
                        @forelse ($conversation as $message)
                            @php $mine = $message->sender_id === $authUser->id; @endphp
                            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[75%] rounded-2xl px-4 py-2 text-sm {{ $mine ? 'bg-[#6366F1] text-white rounded-br-sm' : 'bg-white border border-gray-200 text-gray-800 rounded-bl-sm' }}">
                                    <p>{{ $message->content }}</p>
                                    <p class="mt-1 text-[10px] {{ $mine ? 'text-indigo-100/80' : 'text-gray-400' }} text-right">{{ $message->created_at?->format('H:i') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="h-full flex items-center justify-center text-sm text-gray-400">Démarrez la conversation.</div>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('messages.store') }}" class="border-t border-gray-200 p-3 flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="receiver_id" value="{{ $activeContact->id }}">
                        <input type="text" name="content" required autocomplete="off"
                               class="form-input flex-1" placeholder="Écrire un message...">
                        <button type="submit" class="btn-primary !px-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        </button>
                    </form>
                @else
                    <div class="flex-1 flex flex-col items-center justify-center text-center px-6">
                        <div class="h-14 w-14 rounded-2xl bg-indigo-50 text-[#6366F1] flex items-center justify-center mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-900">Sélectionnez une conversation</p>
                        <p class="text-sm text-gray-500">Choisissez un contact à gauche pour discuter.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($activeContact)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const el = document.getElementById('chat-scroll');
                if (el) { el.scrollTop = el.scrollHeight; }
            });
        </script>
    @endif
</x-app-layout>
