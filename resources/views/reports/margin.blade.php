@extends('layouts.app')

@section('title', 'Gross margin')

@section('content')
    @include('reports._daterange', ['route' => 'reports.margin'])

    <div class="max-w-lg overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <tbody class="divide-y divide-gray-100">
                <tr>
                    <td class="px-4 py-2 text-gray-600">Revenue (sales, ex-VAT)</td>
                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format($report['revenue'], 2) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-gray-600">Cost of goods sold</td>
                    <td class="px-4 py-2 text-right tabular-nums">({{ number_format($report['cogs'], 2) }})</td>
                </tr>
                <tr class="border-t border-gray-200 font-medium">
                    <td class="px-4 py-2">
                        Gross profit
                        @if ($report['gross_margin_pct'] !== null)
                            <span class="ml-1 text-xs text-gray-400">{{ $report['gross_margin_pct'] }}%</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format($report['gross_profit'], 2) }}</td>
                </tr>
                <tr>
                    <td class="px-4 py-2 text-gray-600">Expenses</td>
                    <td class="px-4 py-2 text-right tabular-nums">({{ number_format($report['expenses'], 2) }})</td>
                </tr>
                <tr class="border-t border-gray-200 font-semibold">
                    <td class="px-4 py-2">Operating result</td>
                    <td class="px-4 py-2 text-right tabular-nums {{ $report['operating_result'] < 0 ? 'text-red-600' : '' }}">
                        {{ number_format($report['operating_result'], 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <p class="mt-2 max-w-lg text-xs text-gray-400">
        COGS uses each item's current cost price, not a snapshot from the time of sale —
        an approximation. There is no general ledger; this is not a statutory P&amp;L.
    </p>
@endsection
