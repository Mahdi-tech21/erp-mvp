@extends('layouts.app')

@section('title', 'Party statement')

@section('content')
    <form method="GET" action="{{ route('reports.statement') }}" class="mb-4 flex flex-wrap items-end gap-2">
        <div>
            <label class="block text-xs font-medium text-gray-500">Party</label>
            <select name="party_id" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm">
                <option value="">— select —</option>
                @foreach ($parties as $p)
                    <option value="{{ $p->id }}" @selected($party && $party->id === $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500">Side</label>
            <select name="role" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm">
                <option value="customer" @selected($role === 'customer')>As customer</option>
                <option value="supplier" @selected($role === 'supplier')>As supplier</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500">From</label>
            <input type="date" name="from" value="{{ $from->toDateString() }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500">To</label>
            <input type="date" name="to" value="{{ $to->toDateString() }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm">
        </div>
        <button class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">Apply</button>
    </form>

    @if (! $statement)
        <p class="text-sm text-gray-400">Pick a party to see their statement.</p>
    @else
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Detail</th>
                        <th class="px-4 py-2 text-right">Charge</th>
                        <th class="px-4 py-2 text-right">Payment</th>
                        <th class="px-4 py-2 text-right">Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr class="text-gray-500">
                        <td class="px-4 py-2" colspan="4">Opening balance</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($statement['opening'], 2) }}</td>
                    </tr>
                    @forelse ($statement['rows'] as $row)
                        <tr>
                            <td class="px-4 py-2 text-gray-600">{{ $row['date']->format('Y-m-d') }}</td>
                            <td class="px-4 py-2">{{ $row['label'] }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ $row['charge'] ? number_format($row['charge'], 2) : '' }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ $row['payment'] ? number_format($row['payment'], 2) : '' }}</td>
                            <td class="px-4 py-2 text-right tabular-nums">{{ number_format($row['balance'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">No activity in this period.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200 font-semibold">
                        <td class="px-4 py-2" colspan="4">Closing balance</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($statement['closing'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <p class="mt-2 text-xs text-gray-400">
            Positive balance = {{ $role === 'supplier' ? 'we owe them' : 'they owe us' }}.
        </p>
    @endif
@endsection
