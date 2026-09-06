@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'submit',
    'size' => 'md',
])

@php
    $base = 'inline-flex items-center justify-center gap-1.5 rounded-lg font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1 disabled:opacity-50';

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-3.5 py-2 text-sm',
    ];

    $variants = [
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-500 shadow-sm',
        'secondary' => 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 shadow-sm',
        'danger' => 'border border-red-300 bg-white text-red-600 hover:bg-red-50',
        'ghost' => 'text-gray-500 hover:text-gray-900',
    ];

    $classes = trim("{$base} {$sizes[$size]} {$variants[$variant]}");
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
