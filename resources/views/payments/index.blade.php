@extends('layouts.app')

@section('title', 'Payments')

@section('actions')
    <div class="flex gap-2">
        <a href="{{ route('payments.in.create') }}"
           class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
            Customer payment
        </a>
        <a href="{{ route('payments.out.create') }}"
           class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">
            Supplier payment
        </a>
    </div>
@endsection

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search party or reference&hellip;"
               class="w-64 rounded-md border border-gray-300 px-3 py-1.5 text-sm">
        <select name="direction" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm">
            <option value="">In &amp; out</option>
            <option value="in" @selected($direction === 'in')>Money in</option>
            <option value="out" @selected($direction === 'out')>Money out</option>
        </select>
        <button class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">Search</button>
        @if ($q !== '' || $direction !== '')
            <a href="{{ route('payments.index') }}" class="rounded-md px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Direction</th>
                    <th class="px-4 py-2">Party</th>
                    <th class="px-4 py-2">Method</th>
                    <th class="px-4 py-2">Reference</th>
                    <th class="px-4 py-2 text-right">Amount</th>
                    <th class="px-4 py-2 text-right">Unallocated</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($payments as $payment)
                    @php $unallocated = (float) $payment->amount - (float) ($payment->allocated_total ?? 0); @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <a href="{{ route('payments.show', $payment) }}" class="font-medium text-gray-900 hover:underline">
                                {{ $payment->payment_date->format('Y-m-d') }}
                            </a>
                        </td>
                        <td class="px-4 py-2">
                            @if ($payment->direction === 'in')
                                <span class="rounded bg-green-100 px-1.5 py-0.5 text-xs font-medium text-green-700">In</span>
                            @else
                                <span class="rounded bg-amber-100 px-1.5 py-0.5 text-xs font-medium text-amber-700">Out</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $payment->party->name }}</td>
                        <td class="px-4 py-2 capitalize text-gray-600">{{ $payment->method }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $payment->reference ?: '—' }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums {{ $unallocated > 0.001 ? 'text-amber-600' : 'text-gray-400' }}">
                            {{ number_format($unallocated, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No payments recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
@endsection
