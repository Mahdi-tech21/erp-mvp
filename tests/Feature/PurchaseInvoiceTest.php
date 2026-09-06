<?php

use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Party;
use App\Services\DocumentService;

beforeEach(function () {
    asAdmin();
    CompanySetting::factory()->create(['tax_rate' => 11.00, 'sales_prefix' => 'INV', 'purchase_prefix' => 'BILL']);
});

function purchasePayload(array $overrides = []): array
{
    $supplier = Party::factory()->supplier()->create();

    return array_merge([
        'party_id' => $supplier->id,
        'doc_date' => now()->toDateString(),
        'discount' => 0,
        'external_ref' => 'SUP-9001',
        'lines' => [
            ['item_id' => null, 'description' => 'Raw materials', 'qty' => 10, 'unit_price' => 12],
        ],
    ], $overrides);
}

it('lists purchase invoices only, not sales invoices', function () {
    $purchase = Document::factory()->purchase()->create();
    $sale = Document::factory()->sales()->create();

    $this->get(route('purchase-invoices.index'))
        ->assertOk()
        ->assertSee('Bills')
        ->assertSee($purchase->party->name)
        ->assertDontSee($sale->party->name);
});

it('shows the create form with suppliers and the supplier invoice-number field', function () {
    Party::factory()->supplier()->create(['name' => 'Acme Supplies']);
    Party::factory()->customer()->create(['name' => 'Retail Customer']);

    $this->get(route('purchase-invoices.create'))
        ->assertOk()
        ->assertSee('New Bill')
        ->assertSee('Acme Supplies')
        ->assertDontSee('Retail Customer')
        ->assertSee("Supplier's invoice number", false);
});

it('stores a draft with the external reference', function () {
    $this->post(route('purchase-invoices.store'), purchasePayload());

    $document = Document::latest('id')->first();

    expect($document->doc_type)->toBe('purchase_invoice')
        ->and($document->status)->toBe('draft')
        ->and($document->external_ref)->toBe('SUP-9001')
        ->and($document->subtotal)->toBe('120.00');

    $this->get(route('purchase-invoices.show', $document))->assertSee('SUP-9001');
});

it('rejects a draft whose party is not a supplier', function () {
    $customer = Party::factory()->customer()->create();

    $this->post(route('purchase-invoices.store'), purchasePayload(['party_id' => $customer->id]))
        ->assertSessionHas('error');

    expect(Document::count())->toBe(0);
});

it('posts a bill on its own BILL sequence', function () {
    $this->post(route('purchase-invoices.store'), purchasePayload());
    $bill = Document::latest('id')->first();

    // a sales invoice posted first must not consume the BILL sequence
    $sale = Document::factory()->sales()->create();
    DocumentLine::factory()->for($sale)->create();
    app(DocumentService::class)->post($sale->load('lines', 'party'));

    $this->post(route('purchase-invoices.post', $bill))
        ->assertRedirect(route('purchase-invoices.show', $bill))
        ->assertSessionHas('status');

    expect($bill->fresh()->number)->toBe('BILL-'.now()->year.'-0001')
        ->and($sale->fresh()->number)->toBe('INV-'.now()->year.'-0001');
});

it('does not put the external ref on a sales invoice', function () {
    $customer = Party::factory()->customer()->create();

    $this->post(route('sales-invoices.store'), [
        'party_id' => $customer->id,
        'doc_date' => now()->toDateString(),
        'discount' => 0,
        'external_ref' => 'SHOULD-BE-IGNORED',
        'lines' => [['item_id' => null, 'description' => 'X', 'qty' => 1, 'unit_price' => 5]],
    ]);

    expect(Document::latest('id')->first()->external_ref)->toBeNull();
});

it('404s when a sales invoice is reached through a purchase route', function () {
    $sale = Document::factory()->sales()->create();

    $this->get(route('purchase-invoices.show', $sale))->assertNotFound();
});

it('prints a bill with its external reference', function () {
    $supplier = Party::factory()->supplier()->create();
    $bill = Document::factory()->purchase()->create([
        'party_id' => $supplier->id,
        'status' => 'posted',
        'number' => 'BILL-2026-0007',
        'external_ref' => 'SUP-42',
    ]);

    $this->get(route('purchase-invoices.print', $bill))
        ->assertOk()
        ->assertSee('BILL-2026-0007')
        ->assertSee('SUP-42');
});
