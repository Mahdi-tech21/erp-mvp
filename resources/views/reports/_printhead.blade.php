{{-- Visible only on the printed page. $title, and $range (a string) --}}
<div class="mb-4 hidden print:block">
    <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
    @isset($range)
        <p class="text-xs text-gray-500">{{ $range }}</p>
    @endisset
    <p class="text-xs text-gray-400">{{ config('app.name') }} · generated {{ now()->format('Y-m-d H:i') }}</p>
</div>
