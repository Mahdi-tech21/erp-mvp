@props(['right' => false])

<th {{ $attributes->class([
    'px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500',
    'text-right' => $right,
    'text-left' => ! $right,
]) }}>{{ $slot }}</th>
