<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        $unitPrice = fake()->randomFloat(2, 5, 500);

        return [
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-??')),
            'name' => fake()->words(3, true),
            'type' => 'product',
            'unit_price' => $unitPrice,
            'cost_price' => round($unitPrice * fake()->randomFloat(2, 0.4, 0.8), 2),
            'is_active' => true,
        ];
    }

    public function product(): static
    {
        return $this->state(fn () => ['type' => 'product']);
    }

    public function service(): static
    {
        return $this->state(fn () => ['type' => 'service', 'cost_price' => 0]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
