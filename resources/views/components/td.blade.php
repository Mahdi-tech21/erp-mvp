@props(['right' => false, 'num' => false])

<td {{ $attributes->class([
    'px-4 py-2.5 text-gray-700',
    'text-right' => $right || $num,
    'tabular-nums' => $num,
]) }}>{{ $slot }}</td>
