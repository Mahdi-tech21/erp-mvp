@php
    /** @var \App\Models\Document $document */
    $isEdit = $document->exists;
    $lineInput = 'block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500';

    if (old('lines')) {
        $rows = array_values(old('lines'));
    } elseif ($isEdit && $document->lines->isNotEmpty()) {
        $rows = $document->lines->map(fn ($l) => [
            'id' => $l->id,
            'item_id' => $l->item_id,
            'description' => $l->description,
            'qty' => rtrim(rtrim((string) $l->qty, '0'), '.'),
            'unit_price' => $l->unit_price,
        ])->all();
    } else {
        $rows = [['item_id' => '', 'description' => '', 'qty' => 1, 'unit_price' => 0]];
    }
@endphp

<x-app-layout :title="($isEdit ? 'Edit ' : 'New ') . $type['doc_singular'] . ($isEdit && $document->number ? ' ' . $document->number : '')">
    <form method="POST"
          action="{{ $isEdit ? route($type['route'] . '.update', $document) : route($type['route'] . '.store') }}"
          class="max-w-4xl space-y-6" id="doc-form">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc space-y-0.5 pl-5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <x-card class="grid grid-cols-2 gap-4">
            <x-field class="col-span-2" label="{{ $type['party_singular'] }}" name="party_id">
                <x-select name="party_id">
                    <option value="">— select —</option>
                    @foreach ($parties as $party)
                        <option value="{{ $party->id }}" @selected((int) old('party_id', $document->party_id) === $party->id)>{{ $party->name }}</option>
                    @endforeach
                </x-select>
            </x-field>
            <x-field label="Date" name="doc_date">
                <x-input type="date" name="doc_date" :value="old('doc_date', optional($document->doc_date)->format('Y-m-d') ?? now()->toDateString())" />
            </x-field>
            <x-field label="Due date" name="due_date">
                <x-input type="date" name="due_date" :value="old('due_date', optional($document->due_date)->format('Y-m-d'))" />
            </x-field>
            @if ($type['has_external_ref'])
                <x-field class="col-span-2" label="{{ $type['party_singular'] }}'s invoice number" name="external_ref">
                    <x-input type="text" name="external_ref" :value="old('external_ref', $document->external_ref)" />
                </x-field>
            @endif
        </x-card>

        <x-card flush>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50/70 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="w-56 px-3 py-2.5">Item</th>
                        <th class="px-3 py-2.5">Description</th>
                        <th class="w-24 px-3 py-2.5 text-right">Qty</th>
                        <th class="w-32 px-3 py-2.5 text-right">Unit price</th>
                        @foreach ($lineFields as $field)
                            <th class="w-48 px-3 py-2.5">{{ $field['header'] }}</th>
                        @endforeach
                        <th class="w-32 px-3 py-2.5 text-right">Line total</th>
                        <th class="w-10 px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody id="lines-body" class="divide-y divide-gray-100">
                    @foreach ($rows as $i => $row)
                        <tr class="line-row">
                            <td class="px-3 py-2">
                                <select name="lines[{{ $i }}][item_id]" class="{{ $lineInput }} line-item">
                                    <option value="">—</option>
                                    @foreach ($items as $item)
                                        <option value="{{ $item->id }}" data-price="{{ $item->unit_price }}" data-name="{{ $item->name }}"
                                            @selected((int) ($row['item_id'] ?? 0) === $item->id)>{{ $item->sku }} — {{ $item->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-3 py-2"><input type="text" name="lines[{{ $i }}][description]" value="{{ $row['description'] ?? '' }}" class="{{ $lineInput }} line-desc"></td>
                            <td class="px-3 py-2"><input type="number" step="0.001" min="0" name="lines[{{ $i }}][qty]" value="{{ $row['qty'] ?? 1 }}" class="{{ $lineInput }} text-right line-qty"></td>
                            <td class="px-3 py-2"><input type="number" step="0.01" min="0" name="lines[{{ $i }}][unit_price]" value="{{ $row['unit_price'] ?? 0 }}" class="{{ $lineInput }} text-right line-price"></td>
                            @foreach ($lineFields as $field)
                                <td class="px-3 py-2">@include($field['partial'], ['i' => $i, 'row' => $row])</td>
                            @endforeach
                            <td class="px-3 py-2 text-right tabular-nums text-gray-700 line-total">0.00</td>
                            <td class="px-3 py-2 text-center"><button type="button" class="remove-line text-gray-400 hover:text-red-600">&times;</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-100 px-3 py-2">
                <button type="button" id="add-line" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">+ Add line</button>
            </div>
        </x-card>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-field label="Notes" name="notes">
                <x-textarea name="notes" rows="3">{{ old('notes', $document->notes) }}</x-textarea>
            </x-field>
            <x-card class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Subtotal</span>
                    <span class="tabular-nums" id="sum-subtotal">0.00</span>
                </div>
                <div class="flex items-center justify-between">
                    <label for="discount" class="text-gray-500">Discount</label>
                    <input type="number" step="0.01" min="0" id="discount" name="discount"
                           value="{{ old('discount', $document->discount ?? 0) }}"
                           class="w-28 rounded-md border-gray-300 text-right text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Tax ({{ rtrim(rtrim(number_format($taxRate, 2), '0'), '.') }}%)</span>
                    <span class="tabular-nums" id="sum-tax">0.00</span>
                </div>
                <div class="flex justify-between border-t border-gray-200 pt-2 font-semibold">
                    <span>Total</span>
                    <span class="tabular-nums" id="sum-total">0.00</span>
                </div>
            </x-card>
        </div>

        <div class="flex gap-2">
            <x-btn>Save draft</x-btn>
            <x-btn variant="secondary" :href="$isEdit ? route($type['route'] . '.show', $document) : route($type['route'] . '.index')">Cancel</x-btn>
        </div>
    </form>

    <template id="line-template">
        <tr class="line-row">
            <td class="px-3 py-2">
                <select name="lines[__IDX__][item_id]" class="{{ $lineInput }} line-item">
                    <option value="">—</option>
                    @foreach ($items as $item)
                        <option value="{{ $item->id }}" data-price="{{ $item->unit_price }}" data-name="{{ $item->name }}">{{ $item->sku }} — {{ $item->name }}</option>
                    @endforeach
                </select>
            </td>
            <td class="px-3 py-2"><input type="text" name="lines[__IDX__][description]" class="{{ $lineInput }} line-desc"></td>
            <td class="px-3 py-2"><input type="number" step="0.001" min="0" name="lines[__IDX__][qty]" value="1" class="{{ $lineInput }} text-right line-qty"></td>
            <td class="px-3 py-2"><input type="number" step="0.01" min="0" name="lines[__IDX__][unit_price]" value="0" class="{{ $lineInput }} text-right line-price"></td>
            @foreach ($lineFields as $field)
                <td class="px-3 py-2">@include($field['partial'], ['i' => '__IDX__', 'row' => []])</td>
            @endforeach
            <td class="px-3 py-2 text-right tabular-nums text-gray-700 line-total">0.00</td>
            <td class="px-3 py-2 text-center"><button type="button" class="remove-line text-gray-400 hover:text-red-600">&times;</button></td>
        </tr>
    </template>

    @push('scripts')
    <script>
    (function () {
        const taxRate = {{ $taxRate }};
        const body = document.getElementById('lines-body');
        const tpl = document.getElementById('line-template').innerHTML;
        let nextIdx = {{ count($rows) }};
        const money = n => (Math.round((n + Number.EPSILON) * 100) / 100).toFixed(2);

        function recalc() {
            let subtotal = 0;
            body.querySelectorAll('.line-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.line-qty').value) || 0;
                const price = parseFloat(row.querySelector('.line-price').value) || 0;
                const total = qty * price;
                row.querySelector('.line-total').textContent = money(total);
                subtotal += total;
            });
            const discount = parseFloat(document.getElementById('discount').value) || 0;
            const tax = Math.max(subtotal - discount, 0) * taxRate / 100;
            document.getElementById('sum-subtotal').textContent = money(subtotal);
            document.getElementById('sum-tax').textContent = money(tax);
            document.getElementById('sum-total').textContent = money(subtotal - discount + tax);
        }

        document.getElementById('add-line').addEventListener('click', () => {
            body.insertAdjacentHTML('beforeend', tpl.replace(/__IDX__/g, nextIdx++));
            recalc();
        });
        body.addEventListener('input', recalc);
        body.addEventListener('change', e => {
            if (e.target.classList.contains('line-item')) {
                const opt = e.target.selectedOptions[0];
                const row = e.target.closest('.line-row');
                if (opt && opt.value) {
                    row.querySelector('.line-price').value = opt.dataset.price;
                    const desc = row.querySelector('.line-desc');
                    if (!desc.value) desc.value = opt.dataset.name;
                }
                recalc();
            }
        });
        body.addEventListener('click', e => {
            if (e.target.classList.contains('remove-line') && body.querySelectorAll('.line-row').length > 1) {
                e.target.closest('.line-row').remove();
                recalc();
            }
        });
        document.getElementById('discount').addEventListener('input', recalc);
        recalc();
    })();
    </script>
    @endpush
</x-app-layout>
