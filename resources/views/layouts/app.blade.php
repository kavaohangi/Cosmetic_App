<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title . ' · Premidis' : 'Premidis — CRM Gestion Cosmétique' }}</title>

        <!-- Favicon -->
        @if (file_exists(public_path('images/premidis-logo.png')))
            <link rel="icon" type="image/png" href="{{ asset('images/premidis-logo.png') }}">
        @else
            <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Theme (avant le rendu pour éviter le flash) -->
        <script>
            (function () {
                try {
                    const t = localStorage.getItem('theme');
                    if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                        document.documentElement.classList.add('dark');
                    }
                } catch (e) {}
                window.__toggleTheme = function () {
                    const isDark = document.documentElement.classList.toggle('dark');
                    try { localStorage.setItem('theme', isDark ? 'dark' : 'light'); } catch (e) {}
                };
            })();
        </script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{
                sidebarOpen: false,
                collapsed: false,
                init() { this.collapsed = localStorage.getItem('sidebarCollapsed') === '1'; },
                toggleCollapse() { this.collapsed = !this.collapsed; localStorage.setItem('sidebarCollapsed', this.collapsed ? '1' : '0'); }
             }" class="min-h-screen bg-[#F3F4F6] dark:bg-[#0B1120]">
            <!-- Sidebar -->
            @include('layouts.navigation')

            <!-- Mobile backdrop -->
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
                 class="fixed inset-0 z-30 bg-black/40 lg:hidden"></div>

            <!-- Main column -->
            <div class="content-area flex flex-col min-h-screen transition-all duration-200"
                 :class="collapsed ? 'lg:pl-20' : 'lg:pl-64'">
                <!-- Topbar -->
                <header class="sticky top-0 z-20 bg-white border-b border-gray-200">
                    <div class="flex items-center justify-between gap-4 px-4 sm:px-6 lg:px-8 h-16">
                        <div class="flex items-center gap-3 min-w-0">
                            <button @click="sidebarOpen = true"
                                    class="lg:hidden text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                            </button>
                            <button @click="toggleCollapse()" title="Replier / déplier le menu"
                                    class="hidden lg:inline-flex text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/></svg>
                            </button>
                            <div class="min-w-0 truncate">
                                @isset($header)
                                    {{ $header }}
                                @endisset
                            </div>
                        </div>

                        <div class="flex items-center gap-3 sm:gap-4">
                            <!-- Bascule thème -->
                            <button type="button" onclick="window.__toggleTheme()" title="Changer de thème"
                                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                <svg class="dark:hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                                <svg class="hidden dark:block" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
                            </button>

                            <!-- Notifications -->
                            @php
                                $notifs = Auth::user()->unreadNotifications()->latest()->limit(10)->get();
                                $notifCount = Auth::user()->unreadNotifications()->count();
                            @endphp
                            <x-dropdown align="right" width="80">
                                <x-slot name="trigger">
                                    <button class="relative text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                                        @if ($notifCount > 0)
                                            <span class="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-[#EF4444] ring-2 ring-white dark:ring-[#0B1120]"></span>
                                        @endif
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                        <span class="text-sm font-semibold text-gray-900">Notifications</span>
                                        @if ($notifCount > 0)
                                            <span class="text-xs text-gray-500">{{ $notifCount }} non lue(s)</span>
                                        @endif
                                    </div>
                                    <div class="max-h-72 overflow-y-auto">
                                        @forelse ($notifs as $n)
                                            @php
                                                $data = $n->data;
                                                $type = $data['type'] ?? null;
                                                $link = match ($type) {
                                                    'produit_disponible', 'stock_insuffisant' => route('stock-alerts.index'),
                                                    default => !empty($data['order_id']) ? route('orders.show', $data['order_id']) : route('dashboard'),
                                                };
                                            @endphp
                                            <a href="{{ $link }}"
                                               class="block px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition">
                                                <p class="text-sm text-gray-800">{{ $data['message'] ?? 'Nouvelle notification' }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                                            </a>
                                        @empty
                                            <p class="px-4 py-6 text-sm text-gray-400 text-center">Aucune notification.</p>
                                        @endforelse
                                    </div>
                                    @if ($notifCount > 0)
                                        <form method="POST" action="{{ route('notifications.read-all') }}" class="px-4 py-2 border-t border-gray-100">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium text-[#6366F1] hover:underline w-full text-center">Marquer tout comme lu</button>
                                        </form>
                                    @endif
                                </x-slot>
                            </x-dropdown>

                            <!-- User dropdown -->
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center justify-center h-9 w-9 rounded-full bg-[#6366F1] text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-[#6366F1]/40 overflow-hidden">
                                        <img src="{{ Auth::user()->avatarUrl() }}" alt="{{ Auth::user()->name }}"
                                             class="h-full w-full object-cover">
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-3">
                                        <img src="{{ Auth::user()->avatarUrl() }}" alt="{{ Auth::user()->name }}"
                                             class="h-10 w-10 rounded-full object-cover">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                                        </div>
                                    </div>
                                    <x-dropdown-link :href="route('profile.edit')">{{ __('Profil') }}</x-dropdown-link>
                                    <button type="button" @click="$dispatch('open-modal', 'confirm-logout')"
                                            class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out">
                                        {{ __('Déconnexion') }}
                                    </button>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
                    <div class="max-w-7xl mx-auto">
                        @include('layouts.partials.flash')
                        {{ $slot }}
                    </div>
                </main>
            </div>

            {{-- Logout confirmation modal --}}
            <x-modal name="confirm-logout" :show="false" focusable>
                <form method="POST" action="{{ route('logout') }}" class="p-6">
                    @csrf
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('Confirmer la déconnexion') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('Êtes-vous sûr de vouloir vous déconnecter ?') }}
                    </p>
                    <div class="mt-6 flex justify-end gap-3">
                        <x-secondary-button type="button" x-on:click="$dispatch('close')">
                            {{ __('Annuler') }}
                        </x-secondary-button>
                        <x-danger-button>
                            {{ __('Se déconnecter') }}
                        </x-danger-button>
                    </div>
                </form>
            </x-modal>
        </div>
    </body>
</html>
