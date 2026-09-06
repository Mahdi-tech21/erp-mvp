{{-- $route, $from, $to --}}
<form method="GET" action="{{ route($route) }}" class="mb-4 flex flex-wrap items-end gap-2">
    <div>
        <label class="block text-xs font-medium text-gray-500">From</label>
        <input type="date" name="from" value="{{ $from->toDateString() }}"
               class="rounded-md border border-gray-300 px-3 py-1.5 text-sm">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500">To</label>
        <input type="date" name="to" value="{{ $to->toDateString() }}"
               class="rounded-md border border-gray-300 px-3 py-1.5 text-sm">
    </div>
    <button class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">Apply</button>
</form>
