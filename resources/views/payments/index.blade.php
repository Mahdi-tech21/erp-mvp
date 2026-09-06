<x-app-layout title="Payments">
    <x-slot:actions>
        <x-btn :href="route('payments.in.create')"><x-icon name="plus" class="size-4" /> Customer payment</x-btn>
        <x-btn variant="secondary" :href="route('payments.out.create')">Supplier payment</x-btn>
    </x-slot:actions>

    <x-filter-bar :action="route('payments.index')"
                  :reset="($q !== '' || $direction !== '') ? route('payments.index') : null">
        <x-input type="text" name="q" :value="$q" placeholder="Search party or reference…" class="w-64" />
        <x-select name="direction" class="w-36">
            <option value="">In &amp; out</option>
            <option value="in" @selected($direction === 'in')>Money in</option>
            <option value="out" @selected($direction === 'out')>Money out</option>
        </x-select>
    </x-filter-bar>

    <x-table>
        <x-slot:head>
            <x-th>Date</x-th>
            <x-th>Direction</x-th>
            <x-th>Party</x-th>
            <x-th>Method</x-th>
            <x-th>Reference</x-th>
            <x-th right>Amount</x-th>
            <x-th right>Unallocated</x-th>
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($payments as $payment)
                @php $unallocated = (float) $payment->amount - (float) ($payment->allocated_total ?? 0); @endphp
                <tr class="hover:bg-gray-50/70">
                    <x-td>
                        <a href="{{ route('payments.show', $payment) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                            {{ $payment->payment_date->format('Y-m-d') }}
                        </a>
                    </x-td>
                    <x-td><x-badge :color="$payment->direction === 'in' ? 'green' : 'amber'">{{ $payment->direction === 'in' ? 'In' : 'Out' }}</x-badge></x-td>
                    <x-td class="text-gray-600">{{ $payment->party->name }}</x-td>
                    <x-td class="capitalize text-gray-500">{{ $payment->method }}</x-td>
                    <x-td class="text-gray-500">{{ $payment->reference ?: '—' }}</x-td>
                    <x-td num>{{ number_format($payment->amount, 2) }}</x-td>
                    <x-td num class="{{ $unallocated > 0.001 ? 'text-amber-600' : 'text-gray-400' }}">{{ number_format($unallocated, 2) }}</x-td>
                </tr>
            @empty
                <x-empty :cols="7">No payments recorded.</x-empty>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-4">{{ $payments->links() }}</div>
</x-app-layout>
