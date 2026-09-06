<?php

use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\Item;
use App\Models\Party;
use App\Modules\Clothing\Models\DocumentLineVariant;
use App\Modules\Clothing\Models\ItemVariant;
use App\Support\ModuleRegistry;

beforeEach(function () {
    asAdmin();
    CompanySetting::factory()->create(['tax_rate' => 0]);
});

it('adds a Variant column to the core invoice form when clothing is active', function () {
    Item::factory()->create(['type' => 'product']);

    $this->get(route('sales-invoices.create'))
        ->assertOk()
        ->assertSee('Variant')
        ->assertSee('lines[0][variant_id]', false);
});

it('registers exactly one line field via the seam', function () {
    expect(app(ModuleRegistry::class)->lineFields())->toHaveCount(1)
        ->and(app(ModuleRegistry::class)->lineFields()[0]['header'])->toBe('Variant');
});

it('persists the chosen variant to the module-owned table on save', function () {
    $customer = Party::factory()->customer()->create();
    $item = Item::factory()->create(['type' => 'product', 'unit_price' => 20]);
    $v = ItemVariant::create(['item_id' => $item->id, 'size' => 'M', 'color' => 'Navy', 'sku' => 'SEAM-1', 'stock_qty' => 5, 'reorder_level' => 0]);

    $this->post(route('sales-invoices.store'), [
        'party_id' => $customer->id,
        'doc_date' => now()->toDateString(),
        'discount' => 0,
        'lines' => [
            ['item_id' => $item->id, 'description' => 'Tee', 'qty' => 2, 'unit_price' => 20, 'variant_id' => $v->id],
        ],
    ])->assertRedirect();

    $line = Document::latest('id')->first()->lines->first();

    expect(DocumentLineVariant::where('document_line_id', $line->id)->value('item_variant_id'))->toBe($v->id);
});

it('shows the variant label on the posted document', function () {
    $customer = Party::factory()->customer()->create();
    $item = Item::factory()->create(['type' => 'product']);
    $v = ItemVariant::create(['item_id' => $item->id, 'size' => 'L', 'color' => 'Olive', 'sku' => 'SEAM-2', 'stock_qty' => 10, 'reorder_level' => 0]);

    $this->post(route('sales-invoices.store'), [
        'party_id' => $customer->id, 'doc_date' => now()->toDateString(), 'discount' => 0,
        'lines' => [['item_id' => $item->id, 'description' => 'Tee', 'qty' => 1, 'unit_price' => 10, 'variant_id' => $v->id]],
    ]);
    $document = Document::latest('id')->first();

    $this->get(route('sales-invoices.show', $document))->assertSee('L / Olive');
});
