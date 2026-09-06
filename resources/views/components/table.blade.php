@props(['head' => null])

<div {{ $attributes->class('overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm') }}>
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        @if ($head)
            <thead class="bg-gray-50/70">
                <tr>{{ $head }}</tr>
            </thead>
        @endif
        {{ $slot }}
    </table>
</div>
