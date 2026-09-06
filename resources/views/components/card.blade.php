@props(['pad' => true, 'flush' => false])

<div {{ $attributes->class([
    'rounded-xl border border-gray-200 bg-white shadow-sm',
    'p-5' => $pad && ! $flush,
    'overflow-hidden' => $flush,
]) }}>
    {{ $slot }}
</div>
