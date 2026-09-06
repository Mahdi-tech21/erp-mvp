<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'expense_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'category' => fake()->randomElement(config('expenses.categories')),
            'description' => fake()->boolean(70) ? fake()->sentence(3) : null,
            'supplier_id' => null,
            'amount' => fake()->randomFloat(2, 20, 2000),
            'method' => fake()->randomElement(['cash', 'card', 'transfer', 'cheque']),
            'reference' => fake()->boolean(40) ? fake()->numerify('EXP-#####') : null,
            'notes' => null,
        ];
    }
}
