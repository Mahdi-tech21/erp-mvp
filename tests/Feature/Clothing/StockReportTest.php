<?php

use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\Item;
use App\Models\Party;
use App\Modules\Clothing\Models\DocumentLineVariant;
use App\Modules\Clothing\Models\ItemVariant;
use App\Services\DocumentService;
use App\Support\ModuleRegistry;

beforeEach(function () {
    asAdmin();
    CompanySetting::factory()->create(['tax_rate' => 0]);
});

function stockedVariant(int $stock, float $reorder = 5, ?float $cost = null): ItemVariant
{
    $item = Item::factory()->create(['type' => 'product', 'cost_price' => $cost ?? 4]);

    return ItemVariant::create([
        'item_id' => $item->id, 'size' => 'M', 'color' => 'Navy',
        'sku' => 'R-'.fake()->unique()->numerify('#####'),
        'stock_qty' => $stock, 'reorder_level' => $reorder,
    ]);
}

it('values stock at the last purchase unit cost', function () {
    $variant = stockedVariant(0);
    $supplier = Party::factory()->supplier()->create();

    $bill = Document::create(['doc_type' => 'purchase_invoice', 'party_id' => $supplier->id, 'doc_date' => now(), 'status' => 'draft']);
    $line = $bill->lines()->create(['item_id' => $variant->item_id, 'description' => 'X', 'qty' => 10, 'unit_price' => 12, 'line_total' => 120, 'sort_order' => 0]);
    DocumentLineVariant::create(['document_line_id' => $line->id, 'item_variant_id' => $variant->id]);
    app(DocumentService::class)->post($bill->load('lines', 'party'));

    $this->get(route('clothing.reports.on-hand'))
        ->assertOk()
        ->assertSee($variant->sku)
        ->assertSee('12.00')   // unit cost
        ->assertSee('120.00'); // value = 10 * 12
});

it('falls back to the item cost price when a variant has never been bought', function () {
    stockedVariant(7, cost: 3.5);

    $this->get(route('clothing.reports.on-hand'))
        ->assertOk()
        ->assertSee('3.50')
        ->assertSee('24.50'); // 7 * 3.5
});

it('lists only variants at or below their reorder level', function () {
    stockedVariant(2, reorder: 5); // low
    $ok = stockedVariant(50, reorder: 5); // fine

    $this->get(route('clothing.reports.low'))
        ->assertOk()
        ->assertDontSee($ok->sku);

    expect($this->get(route('clothing.reports.low'))->getContent())
        ->toContain('Shortfall');
});

it('exposes both reports in the Clothing menu section', function () {
    $routes = collect(app(ModuleRegistry::class)->menu())
        ->flatMap(fn ($s) => $s['items'])->pluck('route');

    expect($routes)->toContain('clothing.reports.on-hand')
        ->and($routes)->toContain('clothing.reports.low');
});
