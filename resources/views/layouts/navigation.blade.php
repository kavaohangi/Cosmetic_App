@php
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'team' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'orders' => '<path d="M9 11H1l3-3m0 6 3 3"/><rect x="9" y="3" width="12" height="18" rx="2"/><path d="M13 7h4M13 11h4M13 15h4"/>',
        'products' => '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
        'clients' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'messages' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'alerts' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        'report' => '<path d="M3 3v18h18"/><rect x="7" y="10" width="3" height="7"/><rect x="12" y="6" width="3" height="11"/><rect x="17" y="13" width="3" height="4"/>',
    ];

    $navLinks = match (auth()->user()?->role) {
        \App\Enums\Role::ChefMarketing => [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => $icons['dashboard']],
            ['route' => 'terrain.team', 'label' => 'Mon Équipe', 'icon' => $icons['team']],
            ['route' => 'orders.index', 'label' => 'Commandes à Valider', 'icon' => $icons['orders']],
            ['route' => 'reports.terrain', 'label' => 'Rapports terrain', 'icon' => $icons['report']],
            ['route' => 'stock-alerts.index', 'label' => 'Alertes Stock', 'icon' => $icons['alerts']],
        ],
        \App\Enums\Role::AgentMarketeur => [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => $icons['dashboard']],
            ['route' => 'orders.index', 'label' => 'Mes Commandes', 'icon' => $icons['orders']],
            ['route' => 'products.index', 'label' => 'Stock', 'icon' => $icons['products']],
            ['route' => 'stock-alerts.index', 'label' => 'Réappro produits', 'icon' => $icons['alerts']],
            ['route' => 'terrain.team', 'label' => 'Mon Équipe Terrain', 'icon' => $icons['team']],
            ['route' => 'reports.terrain', 'label' => 'Rapports équipe', 'icon' => $icons['report']],
            ['route' => 'messages.index', 'label' => 'Messagerie', 'icon' => $icons['messages']],
        ],
        \App\Enums\Role::MarketeurTerrain => [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => $icons['dashboard']],
            ['route' => 'products.index', 'label' => 'Catalogue', 'icon' => $icons['products']],
            ['route' => 'messages.index', 'label' => 'Chat Équipe', 'icon' => $icons['messages']],
        ],
        \App\Enums\Role::Magasinier => [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => $icons['dashboard']],
            ['route' => 'products.index', 'label' => 'Stock', 'icon' => $icons['products']],
            ['route' => 'stock-alerts.index', 'label' => 'Alertes Stock', 'icon' => $icons['alerts']],
            ['route' => 'orders.index', 'label' => 'Commandes', 'icon' => $icons['orders']],
        ],
        \App\Enums\Role::Commercial => [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => $icons['dashboard']],
            ['route' => 'orders.index', 'label' => 'Commandes', 'icon' => $icons['orders']],
            ['route' => 'clients.index', 'label' => 'Clients', 'icon' => $icons['clients']],
        ],
        default => [
            ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => $icons['dashboard']],
            ['route' => 'orders.index', 'label' => 'Commandes', 'icon' => $icons['orders']],
            ['route' => 'products.index', 'label' => 'Stock', 'icon' => $icons['products']],
            ['route' => 'stock-alerts.index', 'label' => 'Alertes Stock', 'icon' => $icons['alerts']],
            ['route' => 'clients.index', 'label' => 'Clients', 'icon' => $icons['clients']],
            ['route' => 'terrain.team', 'label' => 'Équipe', 'icon' => $icons['team']],
        ],
    };
@endphp

<aside
    x-cloak
    :class="{ 'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'lg:w-20': collapsed, 'lg:w-64': !collapsed }"
    class="fixed inset-y-0 left-0 z-40 w-64 bg-[#0F172A] flex flex-col transition-all duration-200 lg:translate-x-0">
    <!-- Brand -->
    <div class="flex items-center justify-between h-16 px-4 border-b border-white/5" :class="collapsed ? 'lg:justify-center lg:px-0' : ''">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 min-w-0">
            <x-app-logo mark-class="h-9 w-9 shrink-0" />
            <span class="text-lg font-bold tracking-tight text-white whitespace-nowrap"
                  :class="collapsed ? 'lg:hidden' : ''">Premidis <span class="text-[#8B5CF6]">SARL</span></span>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden text-indigo-100/70 hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    <!-- Nav links -->
    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
        @foreach ($navLinks as $link)
            @php $active = request()->routeIs($link['route']); @endphp
            <a href="{{ route($link['route']) }}" title="{{ $link['label'] }}"
               :class="collapsed ? 'lg:justify-center' : ''"
               class="{{ $active ? 'sidebar-link-active flex items-center gap-3 text-sm font-medium' : 'sidebar-link' }}">
                <svg class="shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $link['icon'] !!}</svg>
                <span :class="collapsed ? 'lg:hidden' : ''">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <!-- Logout -->
    <div class="px-3 py-4 border-t border-white/5">
        <button type="button" @click="$dispatch('open-modal', 'confirm-logout')"
                title="Déconnexion" :class="collapsed ? 'lg:justify-center' : ''" class="sidebar-link w-full">
            <svg class="shrink-0" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span :class="collapsed ? 'lg:hidden' : ''">Déconnexion</span>
        </button>
    </div>
</aside>
