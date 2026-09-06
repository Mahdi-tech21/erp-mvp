@extends('layouts.app')

@section('title', $title)

@section('content')
    <form method="GET" action="{{ route($route) }}" class="mb-4 flex items-end gap-2">
        <div>
            <label class="block text-xs font-medium text-gray-500">As of</label>
            <input type="date" name="as_of" value="{{ $report['as_of']->toDateString() }}"
                   class="rounded-md border border-gray-300 px-3 py-1.5 text-sm">
        </div>
        <button class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">Apply</button>
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-2">{{ $partyLabel }}</th>
                    <th class="px-4 py-2 text-right">Current</th>
                    <th class="px-4 py-2 text-right">1–30</th>
                    <th class="px-4 py-2 text-right">31–60</th>
                    <th class="px-4 py-2 text-right">60+</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($report['rows'] as $row)
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $row['party'] }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($row['current'], 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($row['b1'], 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($row['b2'], 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums {{ $row['b3'] > 0 ? 'text-red-600' : '' }}">{{ number_format($row['b3'], 2) }}</td>
                        <td class="px-4 py-2 text-right font-medium tabular-nums">{{ number_format($row['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Nothing outstanding.</td></tr>
                @endforelse
            </tbody>
            @if ($report['rows'])
                <tfoot>
                    <tr class="border-t border-gray-200 font-semibold">
                        <td class="px-4 py-2 text-right text-gray-500">Total</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($report['totals']['current'], 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($report['totals']['b1'], 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($report['totals']['b2'], 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($report['totals']['b3'], 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($report['totals']['total'], 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endsection
