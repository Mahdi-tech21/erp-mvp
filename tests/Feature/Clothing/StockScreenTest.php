<?php

use App\Models\Item;
use App\Modules\Clothing\Models\ItemVariant;
use App\Modules\Clothing\Models\StockMovement;

beforeEach(fn () => asAdmin());

it('lists variants and filters to low stock', function () {
    $item = Item::factory()->create(['type' => 'product']);
    ItemVariant::create(['item_id' => $item->id, 'size' => 'M', 'color' => 'Red', 'sku' => 'OK-1', 'stock_qty' => 50, 'reorder_level' => 5]);
    ItemVariant::create(['item_id' => $item->id, 'size' => 'L', 'color' => 'Red', 'sku' => 'LOW-1', 'stock_qty' => 2, 'reorder_level' => 5]);

    $this->get(route('clothing.stock.index', ['low' => 1]))
        ->assertOk()
        ->assertSee('LOW-1')
        ->assertDontSee('OK-1');
});

it('adds a variant with opening stock and an opening movement', function () {
    $item = Item::factory()->create(['type' => 'product']);

    $this->post(route('clothing.stock.store'), [
        'item_id' => $item->id, 'size' => 'S', 'color' => 'Green', 'sku' => 'NEW-1',
        'reorder_level' => 4, 'opening_qty' => 25,
    ])->assertRedirect(route('clothing.stock.index'));

    $variant = ItemVariant::firstWhere('sku', 'NEW-1');

    expect($variant->stock_qty)->toBe(25)
        ->and(StockMovement::where('item_variant_id', $variant->id)->where('reference_type', 'opening')->exists())->toBeTrue();
});

it('rejects a duplicate variant sku', function () {
    $item = Item::factory()->create(['type' => 'product']);
    ItemVariant::create(['item_id' => $item->id, 'size' => 'M', 'color' => 'Red', 'sku' => 'DUP', 'stock_qty' => 1, 'reorder_level' => 0]);

    $this->post(route('clothing.stock.store'), [
        'item_id' => $item->id, 'size' => 'L', 'color' => 'Blue', 'sku' => 'DUP', 'reorder_level' => 0,
    ])->assertSessionHasErrors('sku');
});

it('applies a manual stock adjustment', function () {
    $item = Item::factory()->create(['type' => 'product']);
    $variant = ItemVariant::create(['item_id' => $item->id, 'size' => 'M', 'color' => 'Red', 'sku' => 'ADJ-1', 'stock_qty' => 10, 'reorder_level' => 0]);

    $this->post(route('clothing.stock.adjust', $variant), ['delta' => -3, 'note' => 'damaged'])
        ->assertRedirect();

    expect($variant->fresh()->stock_qty)->toBe(7)
        ->and(StockMovement::where('item_variant_id', $variant->id)->where('reference_type', 'adjustment')->where('direction', 'out')->exists())->toBeTrue();
});

it('refuses an adjustment that would go below zero', function () {
    $item = Item::factory()->create(['type' => 'product']);
    $variant = ItemVariant::create(['item_id' => $item->id, 'size' => 'M', 'color' => 'Red', 'sku' => 'ADJ-2', 'stock_qty' => 2, 'reorder_level' => 0]);

    $this->post(route('clothing.stock.adjust', $variant), ['delta' => -5])->assertSessionHas('error');

    expect($variant->fresh()->stock_qty)->toBe(2);
});
