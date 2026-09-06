{{-- $route, $from, $to --}}
<form method="GET" action="{{ route($route) }}" class="no-print mb-4 flex flex-wrap items-end gap-2">
    <div>
        <label class="mb-1 block text-xs font-medium text-gray-500">From</label>
        <x-input type="date" name="from" :value="$from->toDateString()" class="w-auto" />
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-gray-500">To</label>
        <x-input type="date" name="to" :value="$to->toDateString()" class="w-auto" />
    </div>
    <x-btn type="submit" variant="secondary" size="sm">Apply</x-btn>
</form>
