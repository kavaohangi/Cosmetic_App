@php $active = $activeContact && $activeContact->id === $contact->id; @endphp
<a href="{{ route('messages.conversation', $contact) }}"
   class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 hover:bg-gray-50 transition-colors {{ $active ? 'bg-indigo-50' : '' }}">
    <div class="h-10 w-10 rounded-full bg-[#6366F1] flex items-center justify-center text-sm font-semibold text-white shrink-0">
        {{ $initials($contact->name) }}
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-gray-900 truncate">{{ $contact->name }}</p>
        <p class="text-xs text-gray-500 truncate">{{ $contact->role->label() }}</p>
    </div>
</a>
