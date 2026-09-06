@php /** @var \App\Models\Expense $expense */ @endphp

<form method="POST" action="{{ $action }}" class="max-w-2xl space-y-5">
    @csrf
    @if ($method === 'PUT') @method('PUT') @endif

    <x-card class="space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <x-field label="Date" name="expense_date">
                <x-input type="date" name="expense_date"
                         :value="old('expense_date', optional($expense->expense_date)->format('Y-m-d') ?? now()->toDateString())" />
            </x-field>
            <x-field label="Category" name="category">
                <x-select name="category">
                    @foreach (config('expenses.categories') as $c)
                        <option value="{{ $c }}" @selected(old('category', $expense->category) === $c)>{{ $c }}</option>
                    @endforeach
                </x-select>
            </x-field>
        </div>

        <x-field label="Description" name="description">
            <x-input type="text" name="description" :value="old('description', $expense->description)" />
        </x-field>

        <div class="grid grid-cols-2 gap-4">
            <x-field label="Amount" name="amount">
                <x-input type="number" step="0.01" min="0" name="amount" :value="old('amount', $expense->amount)" />
            </x-field>
            <x-field label="Method" name="method">
                <x-select name="method">
                    @foreach (['cash', 'card', 'transfer', 'cheque'] as $m)
                        <option value="{{ $m }}" @selected(old('method', $expense->method) === $m)>{{ ucfirst($m) }}</option>
                    @endforeach
                </x-select>
            </x-field>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <x-field label="Payee (optional)" name="supplier_id">
                <x-select name="supplier_id">
                    <option value="">—</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((int) old('supplier_id', $expense->supplier_id) === $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </x-select>
            </x-field>
            <x-field label="Reference" name="reference">
                <x-input type="text" name="reference" :value="old('reference', $expense->reference)" />
            </x-field>
        </div>

        <x-field label="Notes" name="notes">
            <x-textarea name="notes" rows="2">{{ old('notes', $expense->notes) }}</x-textarea>
        </x-field>
    </x-card>

    <div class="flex gap-2">
        <x-btn>Save</x-btn>
        <x-btn variant="secondary" :href="route('expenses.index')">Cancel</x-btn>
    </div>
</form>
