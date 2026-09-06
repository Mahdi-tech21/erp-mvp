@php
    $styles = [
        'draft' => 'bg-gray-100 text-gray-600',
        'posted' => 'bg-blue-100 text-blue-700',
        'partial' => 'bg-amber-100 text-amber-700',
        'settled' => 'bg-green-100 text-green-700',
        'void' => 'bg-red-100 text-red-700 line-through',
    ];
@endphp
<span class="rounded px-1.5 py-0.5 text-xs font-medium {{ $styles[$status] ?? 'bg-gray-100 text-gray-600' }}">
    {{ ucfirst($status) }}
</span>
