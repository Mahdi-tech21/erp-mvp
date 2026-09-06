<?php

use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\Item;
use App\Models\Party;
use App\Modules\Clothing\Models\ItemVariant;
use App\Modules\Clothing\Models\StockMovement;

beforeEach(function () {
    asAdmin();
    CompanySetting::factory()->create(['tax_rate' => 10]);
});

/**
 * The demo, as one scenario: buy stock, sell some, take payment, watch the
 * numbers move - all through the core screens, the clothing module reacting
 * only via its listeners and the line-fields seam.
 */
it('runs the whole buy -> sell -> settle loop', function () {
    $supplier = Party::factory()->supplier()->create();
    $customer = Party::factory()->customer()->create();
    $item = Item::factory()->create(['type' => 'product', 'unit_price' => 25, 'cost_price' => 11]);
    $variant = ItemVariant::create([
        'item_id' => $item->id, 'size' => 'M', 'color' => 'Navy', 'sku' => 'DEMO-1',
        'stock_qty' => 0, 'reorder_level' => 5,
    ]);

    // 1. Buy 50 shirts from the supplier and post the bill.
    $this->post(route('purchase-invoices.store'), [
        'party_id' => $supplier->id, 'doc_date' => now()->toDateString(), 'discount' => 0,
        'lines' => [['item_id' => $item->id, 'description' => 'Navy tee', 'qty' => 50, 'unit_price' => 11, 'variant_id' => $variant->id]],
    ]);
    $bill = Document::where('doc_type', 'purchase_invoice')->latest('id')->first();
    $this->post(route('purchase-invoices.post', $bill));

    expect($variant->fresh()->stock_qty)->toBe(50);

    // 2. Sell 3 and post the invoice.
    $this->post(route('sales-invoices.store'), [
        'party_id' => $customer->id, 'doc_date' => now()->toDateString(), 'discount' => 0,
        'lines' => [['item_id' => $item->id, 'description' => 'Navy tee', 'qty' => 3, 'unit_price' => 25, 'variant_id' => $variant->id]],
    ]);
    $invoice = Document::where('doc_type', 'sales_invoice')->latest('id')->first();
    $this->post(route('sales-invoices.post', $invoice));

    expect($variant->fresh()->stock_qty)->toBe(47)
        ->and($invoice->fresh()->total)->toBe('82.50'); // 75 + 10% VAT

    // 3. Record the customer's payment against that invoice.
    $this->post(route('payments.in.store'), [
        'party_id' => $customer->id, 'payment_date' => now()->toDateString(),
        'amount' => 82.50, 'method' => 'card',
        'allocations' => [['document_id' => $invoice->id, 'amount' => 82.50]],
    ]);

    expect($invoice->fresh()->status)->toBe('settled')
        ->and(StockMovement::where('item_variant_id', $variant->id)->count())->toBe(2); // in 50, out 3

    // 4. Stock-on-hand report values the remaining 47 at the last purchase cost.
    $this->get(route('clothing.reports.on-hand'))
        ->assertOk()
        ->assertSee('DEMO-1')
        ->assertSee('517.00'); // 47 * 11
});
