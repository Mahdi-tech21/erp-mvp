<?php

use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\Item;
use App\Models\Party;
use App\Services\PaymentService;

beforeEach(function () {
    asAdmin();
    CompanySetting::factory()->create(['tax_rate' => 11.00, 'sales_prefix' => 'INV']);
});

function salesPayload(array $overrides = []): array
{
    $customer = Party::factory()->customer()->create();

    return array_merge([
        'party_id' => $customer->id,
        'doc_date' => now()->toDateString(),
        'discount' => 0,
        'lines' => [
            ['item_id' => null, 'description' => 'Consulting', 'qty' => 2, 'unit_price' => 50],
            ['item_id' => null, 'description' => 'Setup', 'qty' => 1, 'unit_price' => 30],
        ],
    ], $overrides);
}

it('lists sales invoices only, not purchase invoices', function () {
    $sale = Document::factory()->sales()->create();
    $purchase = Document::factory()->purchase()->create();

    $this->get(route('sales-invoices.index'))
        ->assertOk()
        ->assertSee($sale->party->name)
        ->assertDontSee($purchase->party->name);
});

it('shows the create form with customers and items', function () {
    $customer = Party::factory()->customer()->create(['name' => 'Picklist Customer']);
    $supplier = Party::factory()->supplier()->create(['name' => 'Hidden Supplier']);
    Item::factory()->create(['name' => 'Widget']);

    $this->get(route('sales-invoices.create'))
        ->assertOk()
        ->assertSee('New Invoice')
        ->assertSee('Picklist Customer')
        ->assertDontSee('Hidden Supplier');
});

it('stores a draft, computes totals and redirects to it', function () {
    $response = $this->post(route('sales-invoices.store'), salesPayload(['discount' => 10]));

    $document = Document::latest('id')->first();

    $response->assertRedirect(route('sales-invoices.show', $document));

    expect($document->status)->toBe('draft')
        ->and($document->doc_type)->toBe('sales_invoice')
        ->and($document->lines)->toHaveCount(2)
        ->and($document->subtotal)->toBe('130.00')
        ->and($document->tax_amount)->toBe('13.20')   // (130 - 10) * 11%
        ->and($document->total)->toBe('133.20');
});

it('rejects a draft whose party is not a customer', function () {
    $supplier = Party::factory()->supplier()->create();

    $this->post(route('sales-invoices.store'), salesPayload(['party_id' => $supplier->id]))
        ->assertSessionHas('error');

    expect(Document::count())->toBe(0);
});

it('validates the lines', function () {
    $this->post(route('sales-invoices.store'), salesPayload(['lines' => []]))
        ->assertSessionHasErrors('lines');

    $this->post(route('sales-invoices.store'), salesPayload([
        'lines' => [['description' => 'Real line', 'qty' => 0, 'unit_price' => 5]],
    ]))->assertSessionHasErrors('lines.0.qty');
});

it('drops a blank trailing line row before validating', function () {
    $this->post(route('sales-invoices.store'), salesPayload([
        'lines' => [
            ['item_id' => null, 'description' => 'Kept', 'qty' => 1, 'unit_price' => 10],
            ['item_id' => null, 'description' => '', 'qty' => 1, 'unit_price' => 0],
        ],
    ]))->assertRedirect();

    expect(Document::latest('id')->first()->lines)->toHaveCount(1);
});

it('edits a draft but redirects a posted document to show', function () {
    $draft = Document::factory()->sales()->create();

    $this->get(route('sales-invoices.edit', $draft))->assertOk();

    $draft->forceFill(['status' => 'posted', 'number' => 'INV-2026-0001'])->save();

    $this->get(route('sales-invoices.edit', $draft))
        ->assertRedirect(route('sales-invoices.show', $draft))
        ->assertSessionHas('error');
});

it('replaces the lines on update', function () {
    $this->post(route('sales-invoices.store'), salesPayload());
    $document = Document::latest('id')->first();

    $this->put(route('sales-invoices.update', $document), salesPayload([
        'party_id' => $document->party_id,
        'lines' => [['item_id' => null, 'description' => 'Just one', 'qty' => 1, 'unit_price' => 100]],
    ]))->assertRedirect(route('sales-invoices.show', $document));

    expect($document->fresh()->lines)->toHaveCount(1)
        ->and($document->fresh()->subtotal)->toBe('100.00');
});

it('deletes a draft but not a posted document', function () {
    $draft = Document::factory()->sales()->create();
    $this->delete(route('sales-invoices.destroy', $draft))->assertRedirect(route('sales-invoices.index'));
    expect(Document::find($draft->id))->toBeNull();

    $posted = Document::factory()->sales()->create(['status' => 'posted', 'number' => 'INV-2026-0009']);
    $this->delete(route('sales-invoices.destroy', $posted))->assertSessionHas('error');
    expect(Document::find($posted->id))->not->toBeNull();
});

it('posts a draft through the action route', function () {
    $this->post(route('sales-invoices.store'), salesPayload());
    $document = Document::latest('id')->first();

    $this->post(route('sales-invoices.post', $document))
        ->assertRedirect(route('sales-invoices.show', $document))
        ->assertSessionHas('status');

    expect($document->fresh()->status)->toBe('posted')
        ->and($document->fresh()->number)->toBe('INV-'.now()->year.'-0001');
});

it('will not post an empty draft and flashes the reason', function () {
    $document = Document::factory()->sales()->create();

    $this->post(route('sales-invoices.post', $document))->assertSessionHas('error');

    expect($document->fresh()->status)->toBe('draft');
});

it('voids a posted invoice with no allocations', function () {
    $this->post(route('sales-invoices.store'), salesPayload());
    $document = Document::latest('id')->first();
    $this->post(route('sales-invoices.post', $document));

    $this->post(route('sales-invoices.void', $document))->assertSessionHas('status');

    expect($document->fresh()->status)->toBe('void');
});

it('will not void an invoice that has a payment', function () {
    $this->post(route('sales-invoices.store'), salesPayload());
    $document = Document::latest('id')->first();
    $this->post(route('sales-invoices.post', $document));

    app(PaymentService::class)->record([
        'direction' => 'in',
        'party_id' => $document->party_id,
        'payment_date' => now()->toDateString(),
        'amount' => 20,
        'method' => 'cash',
    ], [['document_id' => $document->id, 'amount' => 20]]);

    $this->post(route('sales-invoices.void', $document))->assertSessionHas('error');

    expect($document->fresh()->status)->not->toBe('void');
});

it('renders the print view for a document', function () {
    $document = Document::factory()->sales()->create(['status' => 'posted', 'number' => 'INV-2026-0004']);

    $this->get(route('sales-invoices.print', $document))
        ->assertOk()
        ->assertSee('INV-2026-0004');
});

it('404s when a purchase invoice is reached through a sales route', function () {
    $purchase = Document::factory()->purchase()->create();

    $this->get(route('sales-invoices.show', $purchase))->assertNotFound();
});
