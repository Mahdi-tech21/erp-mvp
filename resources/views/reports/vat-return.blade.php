@extends('layouts.app')

@section('title', 'VAT return')

@section('content')
    @include('reports._daterange', ['route' => 'reports.vat-return'])

    <div class="max-w-lg overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <tbody class="divide-y divide-gray-100">
                <tr>
                    <td class="px-4 py-2 text-gray-600">Output VAT (on sales)</td>
                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format($report['output']['vat'], 2) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-gray-600">Input VAT (on purchases)</td>
                    <td class="px-4 py-2 text-right tabular-nums">({{ number_format($report['input']['vat'], 2) }})</td>
                </tr>
                <tr class="border-t border-gray-200 font-semibold">
                    <td class="px-4 py-2">{{ $report['net_vat'] >= 0 ? 'Net VAT payable' : 'Net VAT reclaimable' }}</td>
                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format(abs($report['net_vat']), 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if ($report['months'])
        <h2 class="mt-6 mb-2 text-sm font-semibold text-gray-700">By month</h2>
        <div class="max-w-lg overflow-hidden rounded-lg border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-2">Month</th>
                        <th class="px-4 py-2 text-right">Output</th>
                        <th class="px-4 py-2 text-right">Input</th>
                        <th class="px-4 py-2 text-right">Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($report['months'] as $m)
                        <tr>
                            <td class="px-4 py-2 text-gray-600">{{ $m['month'] }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ number_format($m['output'], 2) }}</td>
                            <td class="px-4 py-2 text-right tabular-nums text-gray-500">{{ number_format($m['input'], 2) }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ number_format($m['net'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
