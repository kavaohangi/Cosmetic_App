@php
    $unread = $authUser->receivedMessages()->where('lu', false)->count();
@endphp
<header class="sticky top-0 z-20 h-16 bg-white border-b border-gray-200 flex items-center gap-4 px-4 sm:px-6 lg:px-8">
    <button type="button" class="lg:hidden text-gray-500 hover:text-gray-700" @click="sidebarOpen = true">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    <div class="flex-1">
        <h2 class="text-lg font-semibold text-gray-900">@yield('header', 'Tableau de bord')</h2>
    </div>

    <a href="{{ route('messages.index') }}" class="relative text-gray-400 hover:text-gray-600">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
        </svg>
        @if ($unread > 0)
            <span class="absolute -top-1 -right-1 h-4 min-w-4 px-1 rounded-full bg-[#EF4444] text-white text-[10px] font-bold flex items-center justify-center">{{ $unread }}</span>
        @endif
    </a>

    <div class="h-9 w-9 rounded-full bg-[#6366F1] flex items-center justify-center text-sm font-semibold text-white">
        {{ \Illuminate\Support\Str::of($authUser->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}
    </div>
</header>
