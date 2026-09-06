@props(['status'])

@php
    $map = [
        'draft' => ['gray', 'Draft'],
        'posted' => ['blue', 'Posted'],
        'partial' => ['amber', 'Partial'],
        'settled' => ['green', 'Settled'],
        'void' => ['red', 'Void'],
    ];
    [$color, $label] = $map[$status] ?? ['gray', ucfirst($status)];
@endphp

<x-badge :color="$color" {{ $attributes->class(['line-through' => $status === 'void']) }}>{{ $label }}</x-badge>
