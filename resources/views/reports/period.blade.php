@extends('layouts.app')

@section('title', $title)

@section('content')
    @include('reports._daterange')

    @php
        $count = $rows->sum('count');
        $net = $rows->sum(fn ($r) => (float) $r->net);
        $vat = $rows->sum(fn ($r) => (float) $r->vat);
        $total = $rows->sum(fn ($r) => (float) $r->total);
    @endphp

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-2">Day</th>
                    <th class="px-4 py-2 text-right">{{ ucfirst($partyLabel) }}</th>
                    <th class="px-4 py-2 text-right">Net</th>
                    <th class="px-4 py-2 text-right">VAT</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($rows as $row)
                    <tr>
                        <td class="px-4 py-2 text-gray-600">{{ \Illuminate\Support\Carbon::parse($row->day)->format('Y-m-d') }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ $row->count }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $row->net, 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums text-gray-500">{{ number_format((float) $row->vat, 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format((float) $row->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Nothing in this period.</td></tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr class="border-t border-gray-200 font-semibold">
                        <td class="px-4 py-2 text-right text-gray-500">Total</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ $count }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($net, 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($vat, 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($total, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
