<?php

namespace Database\Factories;

use App\Models\Party;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Party>
 */
class PartyFactory extends Factory
{
    protected $model = Party::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'is_customer' => true,
            'is_supplier' => false,
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'address' => fake()->address(),
            'tax_number' => fake()->numerify('TAX-#######'),
            'notes' => null,
            'is_active' => true,
        ];
    }

    public function customer(): static
    {
        return $this->state(fn () => ['is_customer' => true, 'is_supplier' => false]);
    }

    public function supplier(): static
    {
        return $this->state(fn () => ['is_customer' => false, 'is_supplier' => true]);
    }

    public function both(): static
    {
        return $this->state(fn () => ['is_customer' => true, 'is_supplier' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
