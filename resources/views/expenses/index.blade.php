<x-app-layout title="Expenses">
    <x-slot:actions>
        <x-btn :href="route('expenses.create')"><x-icon name="plus" class="size-4" /> New Expense</x-btn>
    </x-slot:actions>

    <x-filter-bar :action="route('expenses.index')"
                  :reset="($q !== '' || $category !== '') ? route('expenses.index') : null">
        <x-input type="text" name="q" :value="$q" placeholder="Search description, reference or payee…" class="w-72" />
        <x-select name="category" class="w-48">
            <option value="">Any category</option>
            @foreach (config('expenses.categories') as $c)
                <option value="{{ $c }}" @selected($category === $c)>{{ $c }}</option>
            @endforeach
        </x-select>
    </x-filter-bar>

    <x-table>
        <x-slot:head>
            <x-th>Date</x-th>
            <x-th>Category</x-th>
            <x-th>Description</x-th>
            <x-th>Payee</x-th>
            <x-th>Method</x-th>
            <x-th right>Amount</x-th>
            <x-th />
        </x-slot:head>
        <tbody class="divide-y divide-gray-100">
            @forelse ($expenses as $expense)
                <tr class="hover:bg-gray-50/70">
                    <x-td class="text-gray-500">{{ $expense->expense_date->format('Y-m-d') }}</x-td>
                    <x-td>
                        <a href="{{ route('expenses.edit', $expense) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $expense->category }}</a>
                    </x-td>
                    <x-td class="text-gray-600">{{ $expense->description ?: '—' }}</x-td>
                    <x-td class="text-gray-500">{{ $expense->supplier?->name ?? '—' }}</x-td>
                    <x-td class="capitalize text-gray-500">{{ $expense->method }}</x-td>
                    <x-td num>{{ number_format($expense->amount, 2) }}</x-td>
                    <x-td right>
                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:text-red-800">Delete</button>
                        </form>
                    </x-td>
                </tr>
            @empty
                <x-empty :cols="7">No expenses recorded.</x-empty>
            @endforelse
        </tbody>
        @if ($expenses->isNotEmpty())
            <tfoot>
                <tr class="border-t border-gray-200 font-semibold text-gray-900">
                    <td colspan="5" class="px-4 py-2.5 text-right text-gray-500">Total (all matching)</td>
                    <td class="px-4 py-2.5 text-right tabular-nums">{{ number_format($total, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </x-table>

    <div class="mt-4">{{ $expenses->links() }}</div>
</x-app-layout>
