@php
    $tone = $tone ?? 'indigo';
    $iconColor = [
        'indigo' => 'text-[#6366F1]',
        'green' => 'text-green-500',
        'orange' => 'text-orange-500',
        'red' => 'text-[#EF4444]',
    ][$tone] ?? 'text-[#6366F1]';

    $valueTone = $valueTone ?? 'gray';
    $valueColor = [
        'gray' => 'text-gray-900',
        'orange' => 'text-orange-500',
        'green' => 'text-green-600',
        'red' => 'text-[#EF4444]',
        'indigo' => 'text-[#6366F1]',
    ][$valueTone] ?? 'text-gray-900';

    $hintTone = $hintTone ?? 'gray';
    $hintColor = [
        'gray' => 'text-gray-400',
        'green' => 'text-green-600',
        'orange' => 'text-orange-500',
        'red' => 'text-[#EF4444]',
    ][$hintTone] ?? 'text-gray-400';
@endphp
<div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
    <div class="flex items-start justify-between gap-3">
        <p class="text-sm font-medium text-gray-500">{{ $label }}</p>
        <span class="{{ $iconColor }} shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                {!! $icon ?? '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>' !!}
            </svg>
        </span>
    </div>
    <p class="mt-3 text-3xl font-bold {{ $valueColor }}">{{ $value }}</p>
    @isset($hint)
        <p class="mt-1 text-xs {{ $hintColor }}">{{ $hint }}</p>
    @endisset
</div>
