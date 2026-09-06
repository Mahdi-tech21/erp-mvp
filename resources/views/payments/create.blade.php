@extends('layouts.app')

@section('title', $config['action'])

@php
    $inputClass = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none';
@endphp

@section('content')
    @if ($errors->any())
        <div class="mb-4 max-w-3xl rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @unless ($party)
        {{-- Step 1: pick the party. Submitting reloads this page with ?party_id set. --}}
        <form method="GET" class="max-w-md space-y-4 rounded-lg border border-gray-200 bg-white p-6">
            <div>
                <label class="text-sm font-medium text-gray-700">{{ $config['party_singular'] }}</label>
                <select name="party_id" class="{{ $inputClass }}" onchange="this.form.submit()">
                    <option value="">— select —</option>
                    @foreach ($parties as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <p class="mt-2 text-xs text-gray-400">Pick a {{ strtolower($config['party_singular']) }} to see their open {{ $config['doc_noun'] }}s.</p>
            </div>
        </form>
    @else
        <form method="POST" action="{{ route('payments.' . $direction . '.store') }}" class="max-w-3xl space-y-6" id="payment-form">
            @csrf

            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-400">{{ $config['party_singular'] }}</div>
                        <div class="font-medium text-gray-900">{{ $party->name }}</div>
                    </div>
                    <a href="{{ route('payments.' . $direction . '.create') }}" class="text-sm text-gray-400 hover:text-gray-700">change</a>
                </div>
                <input type="hidden" name="party_id" value="{{ $party->id }}">

                <div class="mt-4 grid grid-cols-3 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', now()->toDateString()) }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Amount</label>
                        <input type="number" step="0.01" min="0" name="amount" id="payment-amount"
                               value="{{ old('amount') }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Method</label>
                        <select name="method" class="{{ $inputClass }}">
                            @foreach (config('payments.methods') as $method)
                                <option value="{{ $method }}" @selected(old('method') === $method)>{{ ucfirst($method) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="text-sm font-medium text-gray-700">Reference</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" class="{{ $inputClass }}">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="2" class="{{ $inputClass }}">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    Open {{ $config['doc_noun'] }}s
                </div>
                @if ($openDocuments->isEmpty())
                    <p class="px-4 py-6 text-center text-sm text-gray-400">
                        Nothing outstanding. The payment will be recorded unallocated.
                    </p>
                @else
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead class="text-left text-xs font-semibold uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-4 py-2">Number</th>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2 text-right">Total</th>
                                <th class="px-4 py-2 text-right">Remaining</th>
                                <th class="px-4 py-2 text-right">Allocate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($openDocuments as $i => $doc)
                                @php $remaining = round((float) $doc->total - (float) $doc->settled_total, 2); @endphp
                                <tr>
                                    <td class="px-4 py-2 font-medium text-gray-900">{{ $doc->number }}</td>
                                    <td class="px-4 py-2 text-gray-500">{{ $doc->doc_date->format('Y-m-d') }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format($doc->total, 2) }}</td>
                                    <td class="px-4 py-2 text-right tabular-nums">{{ number_format($remaining, 2) }}</td>
                                    <td class="px-4 py-2 text-right">
                                        <input type="hidden" name="allocations[{{ $i }}][document_id]" value="{{ $doc->id }}">
                                        <input type="number" step="0.01" min="0" max="{{ $remaining }}"
                                               name="allocations[{{ $i }}][amount]"
                                               value="{{ old('allocations.' . $i . '.amount') }}"
                                               data-remaining="{{ $remaining }}"
                                               class="alloc w-28 rounded border border-gray-300 px-2 py-1 text-right text-sm">
                                        <button type="button" class="fill ml-1 text-xs text-gray-400 hover:text-gray-700">full</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="flex justify-end gap-6 border-t border-gray-100 px-4 py-2 text-sm">
                        <span class="text-gray-500">Allocated <span id="sum-alloc" class="tabular-nums text-gray-800">0.00</span></span>
                        <span class="text-gray-500">Unallocated <span id="sum-unalloc" class="tabular-nums text-gray-800">0.00</span></span>
                    </div>
                @endif
            </div>

            <div class="flex gap-2">
                <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">
                    Record {{ strtolower($config['noun']) }}
                </button>
                <a href="{{ route('payments.index') }}"
                   class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancel</a>
            </div>
        </form>
    @endunless
@endsection

@if ($party && $openDocuments->isNotEmpty())
    @push('scripts')
    <script>
    (function () {
        const amountInput = document.getElementById('payment-amount');
        const rows = [...document.querySelectorAll('.alloc')];
        const money = n => (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2);

        function recalc() {
            const allocated = rows.reduce((s, r) => s + (parseFloat(r.value) || 0), 0);
            const amount = parseFloat(amountInput.value) || 0;
            document.getElementById('sum-alloc').textContent = money(allocated);
            const unalloc = amount - allocated;
            const el = document.getElementById('sum-unalloc');
            el.textContent = money(unalloc);
            el.classList.toggle('text-red-600', unalloc < -0.001);
        }

        document.querySelectorAll('.fill').forEach(btn => btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            const remaining = parseFloat(input.dataset.remaining);
            const amount = parseFloat(amountInput.value) || 0;
            const allocatedElsewhere = rows.reduce((s, r) => s + (r === input ? 0 : parseFloat(r.value) || 0), 0);
            input.value = money(Math.max(Math.min(remaining, amount - allocatedElsewhere), 0));
            recalc();
        }));

        amountInput.addEventListener('input', recalc);
        rows.forEach(r => r.addEventListener('input', recalc));
        recalc();
    })();
    </script>
    @endpush
@endif
