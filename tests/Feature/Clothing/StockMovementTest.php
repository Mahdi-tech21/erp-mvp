<?php

use App\Exceptions\DomainException;
use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\Item;
use App\Models\Party;
use App\Modules\Clothing\Models\DocumentLineVariant;
use App\Modules\Clothing\Models\ItemVariant;
use App\Modules\Clothing\Models\StockMovement;
use App\Services\DocumentService;

beforeEach(function () {
    asAdmin();
    CompanySetting::factory()->create(['tax_rate' => 0]);
});

function variant(int $stock = 0): ItemVariant
{
    $item = Item::factory()->create(['type' => 'product', 'unit_price' => 20, 'cost_price' => 9]);

    return ItemVariant::create([
        'item_id' => $item->id, 'size' => 'M', 'color' => 'Navy',
        'sku' => 'V-'.fake()->unique()->numerify('#####'), 'stock_qty' => $stock, 'reorder_level' => 3,
    ]);
}

/** Post a document with one variant line of the given qty. */
function postWithVariant(string $type, ItemVariant $variant, int $qty): Document
{
    $roleParty = $type === 'purchase_invoice'
        ? Party::factory()->supplier()->create()
        : Party::factory()->customer()->create();

    $document = Document::create([
        'doc_type' => $type, 'party_id' => $roleParty->id, 'doc_date' => now(), 'status' => 'draft',
    ]);
    $line = $document->lines()->create([
        'item_id' => $variant->item_id, 'description' => 'X', 'qty' => $qty, 'unit_price' => 10,
        'line_total' => $qty * 10, 'sort_order' => 0,
    ]);
    DocumentLineVariant::create(['document_line_id' => $line->id, 'item_variant_id' => $variant->id]);

    return app(DocumentService::class)->post($document->load('lines', 'party'));
}

it('a posted purchase invoice raises variant stock and logs a movement', function () {
    $v = variant(5);

    postWithVariant('purchase_invoice', $v, 12);

    expect($v->fresh()->stock_qty)->toBe(17)
        ->and(StockMovement::where('item_variant_id', $v->id)->where('direction', 'in')->where('qty', 12)->exists())->toBeTrue();
});

it('a posted sales invoice lowers variant stock', function () {
    $v = variant(20);

    postWithVariant('sales_invoice', $v, 8);

    expect($v->fresh()->stock_qty)->toBe(12)
        ->and(StockMovement::where('item_variant_id', $v->id)->where('direction', 'out')->exists())->toBeTrue();
});

it('voiding a document restores the stock it moved', function () {
    $v = variant(10);
    $sale = postWithVariant('sales_invoice', $v, 4);
    expect($v->fresh()->stock_qty)->toBe(6);

    app(DocumentService::class)->void($sale);

    expect($v->fresh()->stock_qty)->toBe(10)
        ->and($sale->fresh()->status)->toBe('void');
});

it('blocks a void that would drive stock negative, and rolls the void back', function () {
    $v = variant(0);
    $purchase = postWithVariant('purchase_invoice', $v, 10); // stock 10
    postWithVariant('sales_invoice', $v, 8);                 // stock 2

    expect(fn () => app(DocumentService::class)->void($purchase))
        ->toThrow(DomainException::class);

    expect($v->fresh()->stock_qty)->toBe(2)
        ->and($purchase->fresh()->status)->toBe('posted');
});

it('a line with no variant moves no stock', function () {
    $v = variant(10);

    $customer = Party::factory()->customer()->create();
    $document = Document::factory()->sales()->create(['party_id' => $customer->id, 'discount' => 0]);
    $document->lines()->create(['item_id' => $v->item_id, 'description' => 'no variant', 'qty' => 3, 'unit_price' => 10, 'line_total' => 30, 'sort_order' => 0]);
    app(DocumentService::class)->post($document->load('lines', 'party'));

    expect($v->fresh()->stock_qty)->toBe(10)
        ->and(StockMovement::count())->toBe(0);
});
