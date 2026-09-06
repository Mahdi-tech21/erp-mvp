@props(['color' => 'gray'])

@php
    $colors = [
        'gray' => 'bg-gray-100 text-gray-600',
        'indigo' => 'bg-indigo-50 text-indigo-700',
        'blue' => 'bg-blue-50 text-blue-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'green' => 'bg-emerald-50 text-emerald-700',
        'red' => 'bg-red-50 text-red-700',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium', $colors[$color] ?? $colors['gray']]) }}>
    {{ $slot }}
</span>
