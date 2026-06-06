@props([
    'withText' => false,
    'markClass' => 'h-9 w-9',
    'textClass' => 'text-lg font-bold tracking-tight',
])

@php
    $hasImage = file_exists(public_path('images/premidis-logo.png'));
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    @if ($hasImage)
        <img src="{{ asset('images/premidis-logo.png') }}" alt="Premidis SARL"
             class="{{ $markClass }} object-contain shrink-0">
    @else
        <svg class="{{ $markClass }} shrink-0" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <linearGradient id="pdLeft" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#38BDF8"/>
                    <stop offset="1" stop-color="#0EA5E9"/>
                </linearGradient>
                <linearGradient id="pdRight" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0" stop-color="#8B5CF6"/>
                    <stop offset="1" stop-color="#5B21B6"/>
                </linearGradient>
            </defs>
            <circle cx="25" cy="32" r="14" stroke="url(#pdLeft)" stroke-width="6"/>
            <circle cx="39" cy="32" r="14" stroke="url(#pdRight)" stroke-width="6"/>
        </svg>
    @endif

    @if ($withText)
        <span class="{{ $textClass }}">
            Premidis<span class="text-[#8B5CF6]"> SARL</span>
        </span>
    @endif
</span>
