@php
    $title = match ($role->value) {
        'chef_marketing' => 'Tableau de bord Chef Marketing',
        'agent_marketeur' => 'Tableau de bord Agent Marketeur',
        'marketeur_terrain' => 'Tableau de bord Terrain',
        'magasinier' => 'Gestion du Stock',
        'directeur' => 'Tableau de bord Direction',
        'admin' => 'Tableau de bord Administration',
        default => 'Tableau de bord',
    };
@endphp
<x-app-layout :title="$title">
    <x-slot name="header">
        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
    </x-slot>

    @switch($role->value)
        @case('chef_marketing')
            @include('dashboard.partials.chef')
            @break
        @case('agent_marketeur')
            @include('dashboard.partials.agent')
            @break
        @case('marketeur_terrain')
            @include('dashboard.partials.terrain')
            @break
        @case('magasinier')
            @include('dashboard.partials.stock')
            @break
        @default
            @include('dashboard.partials.generic')
    @endswitch
</x-app-layout>
