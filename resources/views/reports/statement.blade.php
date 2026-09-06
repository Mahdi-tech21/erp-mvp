<x-app-layout title="Party statement">
    <x-slot:actions>
        @if ($statement)
            <x-btn type="button" variant="secondary" onclick="window.print()">
                <x-icon name="print" class="size-4" /> Print
            </x-btn>
        @endif
    </x-slot:actions>

    @if ($statement)
        @include('reports._printhead', [
            'title' => $statement['party']->name . ' — statement',
            'range' => $from->toDateString() . ' – ' . $to->toDateString() . ' (as ' . $statement['role'] . ')',
        ])
    @endif

    <form method="GET" action="{{ route('reports.statement') }}" class="no-print mb-4 flex flex-wrap items-end gap-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">Party</label>
            <x-select name="party_id" class="w-auto">
                <option value="">— select —</option>
                @foreach ($parties as $p)
                    <option value="{{ $p->id }}" @selected($party && $party->id === $p->id)>{{ $p->name }}</option>
                @endforeach
            </x-select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">Side</label>
            <x-select name="role" class="w-auto">
                <option value="customer" @selected($role === 'customer')>As customer</option>
                <option value="supplier" @selected($role === 'supplier')>As supplier</option>
            </x-select>
        </div>
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

    @if (! $statement)
        <p class="text-sm text-gray-400">Pick a party to see their statement.</p>
    @else
        <x-table>
            <x-slot:head>
                <x-th>Date</x-th>
                <x-th>Detail</x-th>
                <x-th right>Charge</x-th>
                <x-th right>Payment</x-th>
                <x-th right>Balance</x-th>
            </x-slot:head>
            <tbody class="divide-y divide-gray-100">
                <tr class="text-gray-500">
                    <td class="px-4 py-2.5" colspan="4">Opening balance</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($statement['opening'], 2) }}</td>
                </tr>
                @forelse ($statement['rows'] as $row)
                    <tr>
                        <x-td class="text-gray-600">{{ $row['date']->format('Y-m-d') }}</x-td>
                        <x-td>{{ $row['label'] }}</x-td>
                        <x-td num>{{ $row['charge'] ? number_format($row['charge'], 2) : '' }}</x-td>
                        <x-td num>{{ $row['payment'] ? number_format($row['payment'], 2) : '' }}</x-td>
                        <x-td num>{{ number_format($row['balance'], 2) }}</x-td>
                    </tr>
                @empty
                    <x-empty :cols="5">No activity in this period.</x-empty>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="border-t border-gray-200 font-semibold text-gray-900">
                    <td class="px-4 py-2.5" colspan="4">Closing balance</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($statement['closing'], 2) }}</td>
                </tr>
            </tfoot>
        </x-table>
        <p class="mt-2 text-xs text-gray-400">
            Positive balance = {{ $role === 'supplier' ? 'we owe them' : 'they owe us' }}.
        </p>
    @endif
</x-app-layout>
