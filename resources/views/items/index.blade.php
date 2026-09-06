@extends('layouts.app')

@section('title', 'Items')

@section('actions')
    <a href="{{ route('items.create') }}"
       class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
        New Item
    </a>
@endsection

@section('content')
    <form method="GET" class="mb-4 flex gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search name or SKU&hellip;"
               class="w-64 rounded-md border border-gray-300 px-3 py-1.5 text-sm">
        <button class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">
            Search
        </button>
        @if ($q !== '')
            <a href="{{ route('items.index') }}" class="rounded-md px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-2">SKU</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2 text-right">Unit price</th>
                    <th class="px-4 py-2 text-right">Cost</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($items as $item)
                    <tr>
                        <td class="px-4 py-2 font-mono text-xs text-gray-600">{{ $item->sku }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('items.edit', $item) }}" class="font-medium text-gray-900 hover:underline">
                                {{ $item->name }}
                            </a>
                        </td>
                        <td class="px-4 py-2 capitalize text-gray-600">{{ $item->type }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums text-gray-500">{{ number_format($item->cost_price, 2) }}</td>
                        <td class="px-4 py-2">
                            @if ($item->is_active)
                                <span class="text-green-700">Active</span>
                            @else
                                <span class="text-gray-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('items.edit', $item) }}" class="text-gray-500 hover:text-gray-900">Edit</a>
                            <form method="POST" action="{{ route('items.destroy', $item) }}" class="ml-2 inline"
                                  onsubmit="return confirm('Delete {{ addslashes($item->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $items->links() }}
    </div>
@endsection
