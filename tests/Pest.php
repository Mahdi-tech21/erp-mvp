<?php

use App\Models\Document;
use App\Models\DocumentLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Build a draft document with lines, ready to hand to DocumentService::post().
 *
 * @param  list<array{qty: int|float, unit_price: int|float}>  $lines
 * @param  array<string, mixed>  $attributes
 */
function draftDocument(
    string $type = 'sales_invoice',
    array $lines = [['qty' => 1, 'unit_price' => 100]],
    array $attributes = [],
): Document {
    $factory = $type === 'purchase_invoice'
        ? Document::factory()->purchase()
        : Document::factory()->sales();

    $document = $factory->create(array_merge(['discount' => 0], $attributes));

    foreach (array_values($lines) as $i => $line) {
        DocumentLine::factory()->for($document)->create([
            'qty' => $line['qty'],
            'unit_price' => $line['unit_price'],
            'line_total' => round($line['qty'] * $line['unit_price'], 2),
            'sort_order' => $i,
        ]);
    }

    return $document->load('lines', 'party');
}
