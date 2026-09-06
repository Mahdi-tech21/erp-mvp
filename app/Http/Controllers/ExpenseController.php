<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseRequest;
use App\Models\Expense;
use App\Models\Party;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');

        $filtered = Expense::query()
            ->when($q !== '', fn ($query) => $query->where(fn ($sub) => $sub
                ->where('description', 'ilike', "%{$q}%")
                ->orWhere('reference', 'ilike', "%{$q}%")
                ->orWhereHas('supplier', fn ($s) => $s->where('name', 'ilike', "%{$q}%"))))
            ->when($category !== '', fn ($query) => $query->where('category', $category));

        $total = (clone $filtered)->sum('amount');

        $expenses = $filtered
            ->with('supplier')
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('expenses.index', compact('expenses', 'q', 'category', 'total'));
    }

    public function create(): View
    {
        return view('expenses.create', [
            'expense' => new Expense(['expense_date' => now()->toDateString(), 'method' => 'transfer']),
            'suppliers' => $this->suppliers(),
        ]);
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['amount'] = round((float) $data['amount'], 2);

        $expense = Expense::create($data);

        return redirect()->route('expenses.index')
            ->with('status', 'Expense of '.number_format($expense->amount, 2).' recorded.');
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', [
            'expense' => $expense,
            'suppliers' => $this->suppliers(),
        ]);
    }

    public function update(StoreExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $data = $request->validated();
        $data['amount'] = round((float) $data['amount'], 2);

        $expense->update($data);

        return redirect()->route('expenses.index')->with('status', 'Expense updated.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('status', 'Expense deleted.');
    }

    /**
     * @return Collection<int, Party>
     */
    private function suppliers()
    {
        return Party::query()
            ->where('is_supplier', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
