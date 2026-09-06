<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['document.posted', 'document.voided', 'payment.recorded']),
            'auditable_type' => null,
            'auditable_id' => null,
            'summary' => fake()->sentence(),
            'properties' => [],
        ];
    }
}
