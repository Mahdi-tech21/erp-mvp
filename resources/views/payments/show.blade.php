<x-app-layout :title="ucfirst($payment->direction === 'in' ? 'Customer' : 'Supplier') . ' payment — ' . $payment->payment_date->format('Y-m-d')">
    @php $unallocated = round((float) $payment->amount - $payment->allocations->sum('amount'), 2); @endphp

    <div class="max-w-3xl space-y-6">
        <x-card class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-xs uppercase tracking-wide text-gray-400">
                    {{ $payment->direction === 'in' ? 'From customer' : 'To supplier' }}
                </div>
                <div class="font-medium text-gray-900">{{ $payment->party->name }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs uppercase tracking-wide text-gray-400">Amount</div>
                <div class="text-lg font-semibold tabular-nums">{{ number_format($payment->amount, 2) }}</div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wide text-gray-400">Date</div>
                {{ $payment->payment_date->format('Y-m-d') }}
            </div>
            <div class="text-right">
                <div class="text-xs uppercase tracking-wide text-gray-400">Method</div>
                <span class="capitalize">{{ $payment->method }}</span>@if ($payment->reference) &middot; {{ $payment->reference }}@endif
            </div>
        </x-card>

        <x-card flush>
            <div class="border-b border-gray-100 px-4 py-2.5 text-xs font-semibold uppercase tracking-wide text-gray-500">
                Allocated to
            </div>
            @forelse ($payment->allocations as $allocation)
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-2.5 text-sm last:border-0">
                    <a href="{{ route(config('documents.types.' . $allocation->document->doc_type . '.route') . '.show', $allocation->document) }}"
                       class="font-medium text-gray-900 hover:text-indigo-600">
                        {{ $allocation->document->number }}
                    </a>
                    <span class="tabular-nums">{{ number_format($allocation->amount, 2) }}</span>
                </div>
            @empty
                <p class="px-4 py-4 text-sm text-gray-400">Not allocated to any {{ $payment->direction === 'in' ? 'invoice' : 'bill' }}.</p>
            @endforelse
            @if ($unallocated > 0.001)
                <div class="flex items-center justify-between px-4 py-2.5 text-sm text-amber-600">
                    <span>Unallocated</span>
                    <span class="tabular-nums">{{ number_format($unallocated, 2) }}</span>
                </div>
            @endif
        </x-card>

        @if ($payment->notes)
            <x-card class="text-sm text-gray-600">{{ $payment->notes }}</x-card>
        @endif

        <a href="{{ route('payments.index') }}" class="text-sm text-gray-500 hover:text-gray-800">&larr; All payments</a>
    </div>
</x-app-layout>
