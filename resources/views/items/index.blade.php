<x-app-layout title="Items">
    <x-slot:actions>
        <x-btn :href="route('items.create')"><x-icon name="plus" class="size-4" /> New Item</x-btn>
    </x-slot:actions>

    <x-filter-bar :action="route('items.index')" :reset="$q !== '' ? route('items.index') : null">
        <x-input type="text" name="q" :value="$q" placeholder="Search name or SKU…" class="w-64" />
    </x-filter-bar>

    <x-table>
        <x-slot:head>
            <x-th>SKU</x-th>
            <x-th>Name</x-th>
            <x-th>Type</x-th>
            <x-th right>Unit price</x-th>
            <x-th right>Cost</x-th>
            <x-th>Status</x-th>
            <x-th />
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($items as $item)
                <tr class="hover:bg-gray-50/70">
                    <x-td class="font-mono text-xs text-gray-500">{{ $item->sku }}</x-td>
                    <x-td>
                        <a href="{{ route('items.edit', $item) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $item->name }}</a>
                    </x-td>
                    <x-td class="capitalize text-gray-500">{{ $item->type }}</x-td>
                    <x-td num>{{ number_format($item->unit_price, 2) }}</x-td>
                    <x-td num class="text-gray-500">{{ number_format($item->cost_price, 2) }}</x-td>
                    <x-td><x-badge :color="$item->is_active ? 'green' : 'gray'">{{ $item->is_active ? 'Active' : 'Inactive' }}</x-badge></x-td>
                    <x-td right>
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('items.edit', $item) }}" class="text-gray-500 hover:text-gray-900">Edit</a>
                            <form method="POST" action="{{ route('items.destroy', $item) }}"
                                  onsubmit="return confirm('Delete {{ addslashes($item->name) }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </div>
                    </x-td>
                </tr>
            @empty
                <x-empty :cols="7">No items found.</x-empty>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-4">{{ $items->links() }}</div>
</x-app-layout>
