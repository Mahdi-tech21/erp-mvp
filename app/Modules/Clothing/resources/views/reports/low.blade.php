<x-app-layout title="Low stock">
    <x-slot:actions>
        <x-btn type="button" variant="secondary" onclick="window.print()">
            <x-icon name="print" class="size-4" /> Print
        </x-btn>
    </x-slot:actions>

    <div class="mb-4 hidden print:block">
        <h1 class="text-lg font-semibold text-gray-900">Low stock</h1>
        <p class="text-xs text-gray-400">{{ config('app.name') }} · generated {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <x-table>
        <x-slot:head>
            <x-th>SKU</x-th>
            <x-th>Item</x-th>
            <x-th>Variant</x-th>
            <x-th right>In stock</x-th>
            <x-th right>Reorder at</x-th>
            <x-th right>Shortfall</x-th>
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($variants as $variant)
                <tr>
                    <x-td class="font-mono text-xs text-gray-600">
                        <a href="{{ route('clothing.stock.edit', $variant) }}" class="hover:text-indigo-600">{{ $variant->sku }}</a>
                    </x-td>
                    <x-td>{{ $variant->item->name }}</x-td>
                    <x-td class="text-gray-600">{{ $variant->size }} / {{ $variant->color }}</x-td>
                    <x-td num class="text-red-600">{{ $variant->stock_qty }}</x-td>
                    <x-td num class="text-gray-500">{{ $variant->reorder_level }}</x-td>
                    <x-td num>{{ max($variant->reorder_level - $variant->stock_qty, 0) }}</x-td>
                </tr>
            @empty
                <x-empty :cols="6">Nothing is below its reorder level.</x-empty>
            @endforelse
        </tbody>
    </x-table>
</x-app-layout>
