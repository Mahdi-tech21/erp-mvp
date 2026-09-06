@extends('layouts.app')

@section('title', 'Expenses')

@section('actions')
    <a href="{{ route('expenses.create') }}"
       class="rounded-md bg-gray-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
        New Expense
    </a>
@endsection

@section('content')
    <form method="GET" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search description, reference or payee&hellip;"
               class="w-72 rounded-md border border-gray-300 px-3 py-1.5 text-sm">
        <select name="category" class="rounded-md border border-gray-300 px-3 py-1.5 text-sm">
            <option value="">Any category</option>
            @foreach (config('expenses.categories') as $c)
                <option value="{{ $c }}" @selected($category === $c)>{{ $c }}</option>
            @endforeach
        </select>
        <button class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium hover:bg-gray-50">Search</button>
        @if ($q !== '' || $category !== '')
            <a href="{{ route('expenses.index') }}" class="rounded-md px-3 py-1.5 text-sm text-gray-500 hover:text-gray-800">Clear</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                <tr>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Payee</th>
                    <th class="px-4 py-2">Method</th>
                    <th class="px-4 py-2 text-right">Amount</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($expenses as $expense)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 whitespace-nowrap text-gray-600">{{ $expense->expense_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('expenses.edit', $expense) }}" class="font-medium text-gray-900 hover:underline">
                                {{ $expense->category }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $expense->description ?: '—' }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $expense->supplier?->name ?? '—' }}</td>
                        <td class="px-4 py-2 capitalize text-gray-500">{{ $expense->method }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($expense->amount, 2) }}</td>
                        <td class="px-4 py-2 text-right">
                            <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                                  onsubmit="return confirm('Delete this expense?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-800">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No expenses recorded.</td></tr>
                @endforelse
            </tbody>
            @if ($expenses->isNotEmpty())
                <tfoot>
                    <tr class="border-t border-gray-200 font-semibold">
                        <td colspan="5" class="px-4 py-2 text-right text-gray-500">Total (all matching)</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($total, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="mt-4">{{ $expenses->links() }}</div>
@endsection
