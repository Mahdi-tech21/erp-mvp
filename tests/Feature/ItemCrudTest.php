<?php

use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Item;

it('lists and searches items case-insensitively', function () {
    Item::factory()->create(['name' => 'Cotton Shirt', 'sku' => 'SHIRT-01']);
    Item::factory()->create(['name' => 'Leather Belt', 'sku' => 'BELT-01']);

    $this->get(route('items.index', ['q' => 'shirt']))
        ->assertOk()
        ->assertSee('Cotton Shirt')
        ->assertDontSee('Leather Belt');
});

it('creates an item and rounds money to 2dp at storage', function () {
    $this->post(route('items.store'), [
        'sku' => 'WIDGET-1',
        'name' => 'Widget',
        'type' => 'product',
        'unit_price' => '12.349',
        'cost_price' => '4.001',
        'is_active' => '1',
    ])->assertRedirect(route('items.index'));

    $item = Item::firstWhere('sku', 'WIDGET-1');

    expect((string) $item->unit_price)->toBe('12.35')
        ->and((string) $item->cost_price)->toBe('4.00');
});

it('rejects a duplicate sku', function () {
    Item::factory()->create(['sku' => 'DUP-1']);

    $this->post(route('items.store'), [
        'sku' => 'DUP-1',
        'name' => 'Another',
        'type' => 'product',
        'unit_price' => '1',
    ])->assertSessionHasErrors('sku');
});

it('allows keeping the same sku when updating an item', function () {
    $item = Item::factory()->create(['sku' => 'KEEP-1', 'name' => 'Thing']);

    $this->put(route('items.update', $item), [
        'sku' => 'KEEP-1',
        'name' => 'Renamed Thing',
        'type' => $item->type,
        'unit_price' => '9.99',
        'cost_price' => '0',
        'is_active' => '1',
    ])->assertRedirect(route('items.index'));

    expect($item->fresh()->name)->toBe('Renamed Thing');
});

it('rejects an unknown item type', function () {
    $this->post(route('items.store'), [
        'sku' => 'BAD-1',
        'name' => 'Bad',
        'type' => 'widget',
        'unit_price' => '1',
    ])->assertSessionHasErrors('type');
});

it('refuses to delete an item used on a document line', function () {
    $item = Item::factory()->create();
    $document = Document::factory()->create();
    DocumentLine::factory()->create(['document_id' => $document->id, 'item_id' => $item->id]);

    $this->delete(route('items.destroy', $item))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Item::find($item->id))->not->toBeNull();
});
