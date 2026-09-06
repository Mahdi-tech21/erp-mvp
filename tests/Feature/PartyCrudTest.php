<?php

use App\Models\Document;
use App\Models\Party;

beforeEach(fn () => asAdmin());

it('lists only parties of the screen role', function () {
    $customer = Party::factory()->customer()->create(['name' => 'Only Customer']);
    $supplier = Party::factory()->supplier()->create(['name' => 'Only Supplier']);

    $this->get(route('customers.index'))
        ->assertOk()
        ->assertSee('Only Customer')
        ->assertDontSee('Only Supplier');

    $this->get(route('suppliers.index'))
        ->assertSee('Only Supplier')
        ->assertDontSee('Only Customer');
});

it('searches case-insensitively (ilike)', function () {
    Party::factory()->customer()->create(['name' => 'ACME Industries']);
    Party::factory()->customer()->create(['name' => 'Globex']);

    $this->get(route('customers.index', ['q' => 'acme']))
        ->assertSee('ACME Industries')
        ->assertDontSee('Globex');
});

it('creates a customer and forces the role flag on', function () {
    $this->post(route('customers.store'), [
        'name' => 'New Client',
        'email' => 'client@example.com',
        'is_active' => '1',
        // deliberately no is_customer in the payload
    ])->assertRedirect(route('customers.index'));

    $party = Party::firstWhere('name', 'New Client');

    expect($party->is_customer)->toBeTrue()
        ->and($party->is_supplier)->toBeFalse();
});

it('can flag a customer as also a supplier from the shared form', function () {
    $this->post(route('customers.store'), [
        'name' => 'Dual Party',
        'is_supplier' => '1',
        'is_active' => '1',
    ])->assertRedirect();

    $party = Party::firstWhere('name', 'Dual Party');

    expect($party->is_customer)->toBeTrue()
        ->and($party->is_supplier)->toBeTrue();
});

it('requires a name', function () {
    $this->post(route('customers.store'), ['name' => ''])
        ->assertSessionHasErrors('name');
});

it('404s when editing a party through the wrong role url', function () {
    $supplier = Party::factory()->supplier()->create();

    $this->get(route('customers.edit', $supplier))->assertNotFound();
});

it('updates a party', function () {
    $party = Party::factory()->customer()->create(['name' => 'Old Name']);

    $this->put(route('customers.update', $party), [
        'name' => 'New Name',
        'is_active' => '1',
    ])->assertRedirect(route('customers.index'));

    expect($party->fresh()->name)->toBe('New Name');
});

it('deletes a party with no documents or payments', function () {
    $party = Party::factory()->customer()->create();

    $this->delete(route('customers.destroy', $party))->assertRedirect(route('customers.index'));

    expect(Party::find($party->id))->toBeNull();
});

it('refuses to delete a party that has documents', function () {
    $party = Party::factory()->customer()->create();
    Document::factory()->create(['party_id' => $party->id]);

    $this->delete(route('customers.destroy', $party))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Party::find($party->id))->not->toBeNull();
});
