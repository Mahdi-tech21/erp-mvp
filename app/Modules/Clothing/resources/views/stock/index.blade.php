<x-app-layout title="Stock">
    <x-slot:actions>
        <x-btn :href="route('clothing.stock.create')"><x-icon name="plus" class="size-4" /> New Variant</x-btn>
    </x-slot:actions>

    <x-filter-bar :action="route('clothing.stock.index')" :reset="($q !== '' || $low) ? route('clothing.stock.index') : null">
        <x-input type="text" name="q" :value="$q" placeholder="Search SKU or item…" class="w-64" />
        <label class="flex items-center gap-2 pb-1.5 text-sm text-gray-600">
            <input type="checkbox" name="low" value="1" @checked($low) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            Low stock only
        </label>
    </x-filter-bar>

    <x-table>
        <x-slot:head>
            <x-th>SKU</x-th>
            <x-th>Item</x-th>
            <x-th>Variant</x-th>
            <x-th right>In stock</x-th>
            <x-th right>Reorder at</x-th>
            <x-th>Adjust</x-th>
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($variants as $variant)
                <tr class="hover:bg-gray-50/70">
                    <x-td class="font-mono text-xs text-gray-600">
                        <a href="{{ route('clothing.stock.edit', $variant) }}" class="hover:text-indigo-600">{{ $variant->sku }}</a>
                    </x-td>
                    <x-td>{{ $variant->item->name }}</x-td>
                    <x-td class="text-gray-600">{{ $variant->size }} / {{ $variant->color }}</x-td>
                    <x-td num>
                        {{ $variant->stock_qty }}
                        @if ($variant->isLow())<x-badge color="red" class="ml-1">low</x-badge>@endif
                    </x-td>
                    <x-td num class="text-gray-500">{{ $variant->reorder_level }}</x-td>
                    <x-td>
                        <form method="POST" action="{{ route('clothing.stock.adjust', $variant) }}" class="flex items-center gap-1">
                            @csrf
                            <input type="number" name="delta" placeholder="+/-"
                                   class="w-16 rounded border-gray-300 text-right text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button class="rounded-md border border-gray-300 px-2 py-1 text-xs hover:bg-gray-50">Go</button>
                        </form>
                    </x-td>
                </tr>
            @empty
                <x-empty :cols="6">No variants yet.</x-empty>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-4">{{ $variants->links() }}</div>
</x-app-layout>
