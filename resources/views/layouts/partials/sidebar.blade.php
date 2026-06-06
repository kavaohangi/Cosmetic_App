@php
    $icons = [
        'home' => '<path d="M3 9.5 12 3l9 6.5"/><path d="M5 10v10h14V10"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'check' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',
        'cart' => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
        'box' => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/>',
        'doc' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v5h5"/>',
        'chat' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'briefcase' => '<rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
    ];

    $menus = match ($authUser->role) {
        \App\Enums\Role::ChefMarketing => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
            ['label' => 'Mon Équipe', 'route' => 'terrain.team', 'icon' => 'users'],
            ['label' => 'Commandes à Valider', 'route' => 'orders.index', 'icon' => 'check'],
            ['label' => 'Rapports', 'route' => 'terrain.team', 'icon' => 'doc'],
            ['label' => 'Messagerie', 'route' => 'messages.index', 'icon' => 'chat'],
        ],
        \App\Enums\Role::AgentMarketeur => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
            ['label' => 'Mes Commandes', 'route' => 'orders.index', 'icon' => 'cart'],
            ['label' => 'Stock', 'route' => 'products.index', 'icon' => 'box'],
            ['label' => 'Mon Équipe Terrain', 'route' => 'terrain.team', 'icon' => 'users'],
            ['label' => 'Messagerie', 'route' => 'messages.index', 'icon' => 'chat'],
        ],
        \App\Enums\Role::Commercial => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
            ['label' => 'Commandes', 'route' => 'orders.index', 'icon' => 'cart'],
            ['label' => 'Clients', 'route' => 'clients.index', 'icon' => 'briefcase'],
            ['label' => 'Messagerie', 'route' => 'messages.index', 'icon' => 'chat'],
        ],
        \App\Enums\Role::Magasinier => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
            ['label' => 'Stock', 'route' => 'products.index', 'icon' => 'box'],
            ['label' => 'Commandes', 'route' => 'orders.index', 'icon' => 'cart'],
            ['label' => 'Messagerie', 'route' => 'messages.index', 'icon' => 'chat'],
        ],
        default => [ // Admin / Directeur
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'home'],
            ['label' => 'Commandes', 'route' => 'orders.index', 'icon' => 'cart'],
            ['label' => 'Stock', 'route' => 'products.index', 'icon' => 'box'],
            ['label' => 'Clients', 'route' => 'clients.index', 'icon' => 'briefcase'],
            ['label' => 'Équipe', 'route' => 'terrain.team', 'icon' => 'users'],
            ['label' => 'Messagerie', 'route' => 'messages.index', 'icon' => 'chat'],
        ],
    };
@endphp

{{-- Overlay mobile --}}
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
     class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

<aside
    class="fixed inset-y-0 left-0 z-40 w-64 bg-[#1E1B4B] text-white flex flex-col transition-transform duration-200 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <div class="h-16 flex items-center gap-2.5 px-6 border-b border-white/10">
        <div class="h-9 w-9 rounded-lg bg-[#6366F1] flex items-center justify-center font-bold text-white">P</div>
        <span class="text-lg font-bold tracking-tight">Premidis<span class="text-[#6366F1]"> CRM</span></span>
    </div>

    <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
        @foreach ($menus as $item)
            <a href="{{ route($item['route']) }}"
               class="{{ request()->routeIs($item['route']) ? 'sidebar-link sidebar-link-active' : 'sidebar-link' }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    {!! $icons[$item['icon']] !!}
                </svg>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="border-t border-white/10 p-4">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-full bg-[#6366F1] flex items-center justify-center text-sm font-semibold">
                {{ \Illuminate\Support\Str::of($authUser->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium truncate">{{ $authUser->name }}</p>
                <p class="text-xs text-indigo-200/70 truncate">{{ $authUser->role->label() }}</p>
            </div>
        </div>
        <form method="POST" action="{{ url('/logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full text-left text-sm text-indigo-200/70 hover:text-white transition-colors">
                Déconnexion
            </button>
        </form>
    </div>
</aside>
