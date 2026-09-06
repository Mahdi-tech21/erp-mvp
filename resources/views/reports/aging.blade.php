<x-app-layout :title="$title">
    <x-slot:actions>
        <x-btn type="button" variant="secondary" onclick="window.print()">
            <x-icon name="print" class="size-4" /> Print
        </x-btn>
    </x-slot:actions>

    @include('reports._printhead', ['range' => 'As of ' . $report['as_of']->toDateString()])

    <form method="GET" action="{{ route($route) }}" class="no-print mb-4 flex items-end gap-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">As of</label>
            <x-input type="date" name="as_of" :value="$report['as_of']->toDateString()" class="w-auto" />
        </div>
        <x-btn type="submit" variant="secondary" size="sm">Apply</x-btn>
    </form>

    <x-table>
        <x-slot:head>
            <x-th>{{ $partyLabel }}</x-th>
            <x-th right>Current</x-th>
            <x-th right>1–30</x-th>
            <x-th right>31–60</x-th>
            <x-th right>60+</x-th>
            <x-th right>Total</x-th>
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($report['rows'] as $row)
                <tr>
                    <x-td class="font-medium text-gray-900">{{ $row['party'] }}</x-td>
                    <x-td num>{{ number_format($row['current'], 2) }}</x-td>
                    <x-td num>{{ number_format($row['b1'], 2) }}</x-td>
                    <x-td num>{{ number_format($row['b2'], 2) }}</x-td>
                    <x-td num class="{{ $row['b3'] > 0 ? 'text-red-600' : '' }}">{{ number_format($row['b3'], 2) }}</x-td>
                    <x-td num class="font-medium">{{ number_format($row['total'], 2) }}</x-td>
                </tr>
            @empty
                <x-empty :cols="6">Nothing outstanding.</x-empty>
            @endforelse
        </tbody>
        @if ($report['rows'])
            <tfoot>
                <tr class="border-t border-gray-200 font-semibold text-gray-900">
                    <td class="px-4 py-2.5 text-right text-gray-500">Total</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($report['totals']['current'], 2) }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($report['totals']['b1'], 2) }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($report['totals']['b2'], 2) }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($report['totals']['b3'], 2) }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($report['totals']['total'], 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </x-table>
</x-app-layout>
