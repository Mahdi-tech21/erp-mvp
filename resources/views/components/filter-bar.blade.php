@props(['action', 'reset' => null])

<form method="GET" action="{{ $action }}" {{ $attributes->class('no-print mb-4 flex flex-wrap items-end gap-2') }}>
    {{ $slot }}
    <x-btn type="submit" variant="secondary" size="sm">
        <x-icon name="search" class="size-4" /> Apply
    </x-btn>
    @if ($reset)
        <a href="{{ $reset }}" class="px-2 py-1.5 text-sm text-gray-500 hover:text-gray-800">Clear</a>
    @endif
</form>
