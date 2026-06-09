<x-app-layout title="Messages">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
    </x-slot>

    @php
        $authUser = auth()->user();
        $initials = fn ($name) => \Illuminate\Support\Str::of($name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
    @endphp

    <div class="max-w-2xl mx-auto card p-0 overflow-hidden flex flex-col" style="height: calc(100vh - 8rem)">
        {{-- En-tête : marketeur --}}
        <div class="px-4 py-3 border-b border-gray-200 flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-[#6366F1] flex items-center justify-center text-sm font-semibold text-white">{{ $initials($agent->name) }}</div>
            <div>
                <p class="text-sm font-semibold text-gray-900">{{ $agent->name }}</p>
                <p class="text-xs text-gray-500">Votre marketeur</p>
            </div>
        </div>

        {{-- Conversation --}}
        <div class="flex-1 overflow-y-auto px-4 py-5 space-y-3 bg-[#F9FAFB]" id="chat-scroll">
            @forelse ($conversation as $message)
                @php $mine = $message->sender_id === $authUser->id; @endphp
                <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%] rounded-2xl px-4 py-2 text-sm {{ $mine ? 'bg-[#6366F1] text-white rounded-br-sm' : 'bg-white border border-gray-200 text-gray-800 rounded-bl-sm' }}">
                        <p>{{ $message->content }}</p>
                        <p class="mt-1 text-[10px] {{ $mine ? 'text-indigo-100/80' : 'text-gray-400' }} text-right">{{ $message->created_at?->format('d/m H:i') }}</p>
                    </div>
                </div>
            @empty
                <div class="h-full flex items-center justify-center text-sm text-gray-400">Démarrez la conversation avec votre marketeur.</div>
            @endforelse
        </div>

        {{-- Saisie --}}
        <form method="POST" action="{{ route('portal.messages.send') }}" class="border-t border-gray-200 p-3 flex items-center gap-2">
            @csrf
            <input type="text" name="content" required autocomplete="off"
                   class="form-input flex-1" placeholder="Écrire un message...">
            <button type="submit" class="btn-primary !px-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('chat-scroll');
            if (el) { el.scrollTop = el.scrollHeight; }
        });
    </script>
</x-app-layout>
