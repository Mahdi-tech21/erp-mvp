@php
    /** @var \App\Models\Expense $expense */
    $inputClass = 'mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-gray-500 focus:outline-none';
@endphp

<form method="POST" action="{{ $action }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    @if ($errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Date</label>
            <input type="date" name="expense_date"
                   value="{{ old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?? now()->toDateString()) }}"
                   class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Category</label>
            <select name="category" class="{{ $inputClass }}">
                @foreach (config('expenses.categories') as $c)
                    <option value="{{ $c }}" @selected(old('category', $expense->category) === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700">Description</label>
        <input type="text" name="description" value="{{ old('description', $expense->description) }}" class="{{ $inputClass }}">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Amount</label>
            <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $expense->amount) }}" class="{{ $inputClass }}">
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Method</label>
            <select name="method" class="{{ $inputClass }}">
                @foreach (['cash', 'card', 'transfer', 'cheque'] as $m)
                    <option value="{{ $m }}" @selected(old('method', $expense->method) === $m)>{{ ucfirst($m) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-medium text-gray-700">Payee (optional)</label>
            <select name="supplier_id" class="{{ $inputClass }}">
                <option value="">—</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected((int) old('supplier_id', $expense->supplier_id) === $supplier->id)>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700">Reference</label>
            <input type="text" name="reference" value="{{ old('reference', $expense->reference) }}" class="{{ $inputClass }}">
        </div>
    </div>

    <div>
        <label class="text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" rows="2" class="{{ $inputClass }}">{{ old('notes', $expense->notes) }}</textarea>
    </div>

    <div class="flex gap-2">
        <button class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">Save</button>
        <a href="{{ route('expenses.index') }}"
           class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancel</a>
    </div>
</form>
