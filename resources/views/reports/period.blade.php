<x-app-layout :title="$title">
    <x-slot:actions>
        <x-btn type="button" variant="secondary" onclick="window.print()">
            <x-icon name="print" class="size-4" /> Print
        </x-btn>
    </x-slot:actions>

    @include('reports._printhead', ['range' => $from->toDateString() . ' – ' . $to->toDateString()])
    @include('reports._daterange')

    @php
        $count = $rows->sum('count');
        $net = $rows->sum(fn ($r) => (float) $r->net);
        $vat = $rows->sum(fn ($r) => (float) $r->vat);
        $total = $rows->sum(fn ($r) => (float) $r->total);
    @endphp

    <x-table>
        <x-slot:head>
            <x-th>Day</x-th>
            <x-th right>{{ ucfirst($partyLabel) }}</x-th>
            <x-th right>Net</x-th>
            <x-th right>VAT</x-th>
            <x-th right>Total</x-th>
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($rows as $row)
                <tr>
                    <x-td class="text-gray-600">{{ \Illuminate\Support\Carbon::parse($row->day)->format('Y-m-d') }}</x-td>
                    <x-td num>{{ $row->count }}</x-td>
                    <x-td num>{{ number_format((float) $row->net, 2) }}</x-td>
                    <x-td num class="text-gray-500">{{ number_format((float) $row->vat, 2) }}</x-td>
                    <x-td num>{{ number_format((float) $row->total, 2) }}</x-td>
                </tr>
            @empty
                <x-empty :cols="5">Nothing in this period.</x-empty>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr class="border-t border-gray-200 font-semibold text-gray-900">
                    <td class="px-4 py-2.5 text-right text-gray-500">Total</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ $count }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($net, 2) }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($vat, 2) }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($total, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </x-table>
</x-app-layout>
