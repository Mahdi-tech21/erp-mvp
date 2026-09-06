<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\Party;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'doc_type' => 'sales_invoice',
            'number' => null,
            'party_id' => Party::factory()->customer(),
            'doc_date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'due_date' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'status' => 'draft',
            'subtotal' => 0,
            'discount' => 0,
            'tax_amount' => 0,
            'total' => 0,
            'settled_total' => 0,
            'external_ref' => null,
            'notes' => null,
            'posted_at' => null,
        ];
    }

    public function sales(): static
    {
        return $this->state(fn () => [
            'doc_type' => 'sales_invoice',
            'party_id' => Party::factory()->customer(),
        ]);
    }

    public function purchase(): static
    {
        return $this->state(fn () => [
            'doc_type' => 'purchase_invoice',
            'party_id' => Party::factory()->supplier(),
            'external_ref' => fake()->numerify('SUP-#####'),
        ]);
    }
}
