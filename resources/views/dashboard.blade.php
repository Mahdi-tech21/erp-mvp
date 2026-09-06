<x-app-layout title="Dashboard">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($tiles as $tile)
            @php
                $href = empty($tile['route']) ? null : route($tile['route']);
                $classes = 'block rounded-xl border border-gray-200 bg-white p-5 shadow-sm'
                    .($href ? ' transition hover:border-indigo-300 hover:shadow' : '');
            @endphp

            <a @if ($href) href="{{ $href }}" @endif class="{{ $classes }}">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-500">{{ $tile['label'] }}</span>
                    @if (! empty($tile['icon']))
                        <x-icon :name="$tile['icon']" class="size-4 text-gray-300" />
                    @endif
                </div>
                <div class="mt-2 text-2xl font-semibold tracking-tight text-gray-900 tabular-nums">{{ $tile['value'] }}</div>
                @if (! empty($tile['hint']))
                    <div class="mt-1 text-xs text-gray-400">{{ $tile['hint'] }}</div>
                @endif
            </a>
        @endforeach
    </div>
</x-app-layout>
