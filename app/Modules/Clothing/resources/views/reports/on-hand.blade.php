<x-app-layout title="Stock on hand">
    <x-slot:actions>
        <x-btn type="button" variant="secondary" onclick="window.print()">
            <x-icon name="print" class="size-4" /> Print
        </x-btn>
    </x-slot:actions>

    <div class="mb-4 hidden print:block">
        <h1 class="text-lg font-semibold text-gray-900">Stock on hand</h1>
        <p class="text-xs text-gray-400">{{ config('app.name') }} · generated {{ now()->format('Y-m-d H:i') }}</p>
    </div>

    <x-table>
        <x-slot:head>
            <x-th>SKU</x-th>
            <x-th>Item</x-th>
            <x-th>Variant</x-th>
            <x-th right>Qty</x-th>
            <x-th right>Unit cost</x-th>
            <x-th right>Value</x-th>
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($rows as $row)
                <tr>
                    <x-td class="font-mono text-xs text-gray-600">{{ $row['variant']->sku }}</x-td>
                    <x-td>{{ $row['variant']->item->name }}</x-td>
                    <x-td class="text-gray-600">{{ $row['variant']->size }} / {{ $row['variant']->color }}</x-td>
                    <x-td num>{{ $row['qty'] }}</x-td>
                    <x-td num class="text-gray-500">{{ number_format($row['unit_cost'], 2) }}</x-td>
                    <x-td num>{{ number_format($row['value'], 2) }}</x-td>
                </tr>
            @empty
                <x-empty :cols="6">No variants.</x-empty>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr class="border-t border-gray-200 font-semibold text-gray-900">
                    <td class="px-4 py-2.5 text-right text-gray-500" colspan="3">Total</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($total_qty) }}</td>
                    <td></td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($total_value, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </x-table>

    <p class="mt-2 text-xs text-gray-400">
        Valuation uses each variant's most recent purchase unit cost (or the item's cost price if it has never been bought).
    </p>
</x-app-layout>
