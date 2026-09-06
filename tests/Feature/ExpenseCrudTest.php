<?php

use App\Models\Expense;
use App\Models\Party;

beforeEach(fn () => asAdmin());

it('lists expenses with a total of the matching rows', function () {
    Expense::factory()->create(['category' => 'Rent', 'description' => 'Shop rent', 'amount' => 1000]);
    Expense::factory()->create(['category' => 'Utilities', 'description' => 'Power bill', 'amount' => 250]);

    $this->get(route('expenses.index'))
        ->assertOk()
        ->assertSee('Shop rent')
        ->assertSee('1,250.00'); // total of all matching rows

    $this->get(route('expenses.index', ['category' => 'Rent']))
        ->assertSee('Shop rent')
        ->assertSee('1,000.00')
        ->assertDontSee('Power bill');
});

it('searches by payee name', function () {
    $supplier = Party::factory()->supplier()->create(['name' => 'Landlord Ltd']);
    Expense::factory()->create(['supplier_id' => $supplier->id, 'description' => 'March rent']);
    Expense::factory()->create(['description' => 'Coffee']);

    $this->get(route('expenses.index', ['q' => 'landlord']))
        ->assertSee('March rent')
        ->assertDontSee('Coffee');
});

it('records an expense and rounds the amount', function () {
    $this->post(route('expenses.store'), [
        'expense_date' => now()->toDateString(),
        'category' => 'Fuel & transport',
        'description' => 'Van diesel',
        'amount' => '84.239',
        'method' => 'card',
    ])->assertRedirect(route('expenses.index'));

    $expense = Expense::firstWhere('description', 'Van diesel');

    expect((string) $expense->amount)->toBe('84.24')
        ->and($expense->category)->toBe('Fuel & transport');
});

it('can link an expense to a supplier payee', function () {
    $supplier = Party::factory()->supplier()->create();

    $this->post(route('expenses.store'), [
        'expense_date' => now()->toDateString(),
        'category' => 'Professional fees',
        'supplier_id' => $supplier->id,
        'amount' => '500',
        'method' => 'transfer',
    ])->assertRedirect();

    expect(Expense::latest('id')->first()->supplier_id)->toBe($supplier->id);
});

it('validates amount and method', function () {
    $this->post(route('expenses.store'), [
        'expense_date' => now()->toDateString(),
        'category' => 'Other',
        'amount' => 0,
        'method' => 'crypto',
    ])->assertSessionHasErrors(['amount', 'method']);
});

it('renders the edit form for an expense', function () {
    $expense = Expense::factory()->create(['category' => 'Insurance', 'description' => 'Annual cover']);

    $this->get(route('expenses.edit', $expense))
        ->assertOk()
        ->assertSee('Annual cover')
        ->assertSee('Insurance');
});

it('updates an expense', function () {
    $expense = Expense::factory()->create(['amount' => 100]);

    $this->put(route('expenses.update', $expense), [
        'expense_date' => $expense->expense_date->toDateString(),
        'category' => $expense->category,
        'amount' => '175.50',
        'method' => $expense->method,
    ])->assertRedirect(route('expenses.index'));

    expect((string) $expense->fresh()->amount)->toBe('175.50');
});

it('deletes an expense', function () {
    $expense = Expense::factory()->create();

    $this->delete(route('expenses.destroy', $expense))->assertRedirect(route('expenses.index'));

    expect(Expense::find($expense->id))->toBeNull();
});

it('refuses to delete a supplier that has expenses', function () {
    $supplier = Party::factory()->supplier()->create();
    Expense::factory()->create(['supplier_id' => $supplier->id]);

    $this->delete(route('suppliers.destroy', $supplier))->assertSessionHas('error');

    expect(Party::find($supplier->id))->not->toBeNull();
});
