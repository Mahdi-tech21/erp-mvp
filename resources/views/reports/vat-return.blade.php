<x-app-layout title="VAT return">
    <x-slot:actions>
        <x-btn type="button" variant="secondary" onclick="window.print()">
            <x-icon name="print" class="size-4" /> Print
        </x-btn>
    </x-slot:actions>

    @include('reports._printhead', [
        'title' => 'VAT return',
        'range' => $from->toDateString() . ' – ' . $to->toDateString(),
    ])
    @include('reports._daterange', ['route' => 'reports.vat-return'])

    <x-card flush class="max-w-lg">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <tbody class="divide-y divide-gray-100">
                <tr>
                    <td class="px-4 py-2.5 text-gray-600">Output VAT (on sales)</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($report['output']['vat'], 2) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2.5 text-gray-600">Input VAT (on purchases)</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">({{ number_format($report['input']['vat'], 2) }})</td>
                </tr>
                <tr class="border-t border-gray-200 font-semibold text-gray-900">
                    <td class="px-4 py-2.5">{{ $report['net_vat'] >= 0 ? 'Net VAT payable' : 'Net VAT reclaimable' }}</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format(abs($report['net_vat']), 2) }}</td>
                </tr>
            </tbody>
        </table>
    </x-card>

    @if ($report['months'])
        <h2 class="mb-2 mt-6 text-sm font-semibold text-gray-700">By month</h2>
        <x-table class="max-w-lg">
            <x-slot:head>
                <x-th>Month</x-th>
                <x-th right>Output</x-th>
                <x-th right>Input</x-th>
                <x-th right>Net</x-th>
            </x-slot:head>
            <tbody class="divide-y divide-gray-100">
                @foreach ($report['months'] as $m)
                    <tr>
                        <x-td class="text-gray-600">{{ $m['month'] }}</x-td>
                        <x-td num>{{ number_format($m['output'], 2) }}</x-td>
                        <x-td num class="text-gray-500">{{ number_format($m['input'], 2) }}</x-td>
                        <x-td num>{{ number_format($m['net'], 2) }}</x-td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>
    @endif
</x-app-layout>
