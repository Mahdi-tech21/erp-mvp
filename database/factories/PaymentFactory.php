<?php

namespace Database\Factories;

use App\Models\Party;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'direction' => 'in',
            'party_id' => Party::factory()->customer(),
            'payment_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'amount' => fake()->randomFloat(2, 20, 2000),
            'method' => fake()->randomElement(['cash', 'card', 'transfer', 'cheque']),
            'reference' => fake()->optional()->numerify('REF-#####'),
            'notes' => null,
        ];
    }

    public function in(): static
    {
        return $this->state(fn () => [
            'direction' => 'in',
            'party_id' => Party::factory()->customer(),
        ]);
    }

    public function out(): static
    {
        return $this->state(fn () => [
            'direction' => 'out',
            'party_id' => Party::factory()->supplier(),
        ]);
    }
}
