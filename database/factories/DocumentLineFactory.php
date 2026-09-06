<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentLine;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DocumentLine>
 */
class DocumentLineFactory extends Factory
{
    protected $model = DocumentLine::class;

    public function definition(): array
    {
        $qty = fake()->numberBetween(1, 20);
        $unitPrice = fake()->randomFloat(2, 5, 500);

        return [
            'document_id' => Document::factory(),
            'item_id' => Item::factory(),
            'description' => fake()->words(3, true),
            'qty' => $qty,
            'unit_price' => $unitPrice,
            'line_total' => round($qty * $unitPrice, 2),
            'meta' => null,
            'sort_order' => 0,
        ];
    }
}
