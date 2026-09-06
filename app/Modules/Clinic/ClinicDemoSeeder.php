<?php

namespace App\Modules\Clinic;

use App\Models\Item;
use App\Models\Party;
use App\Modules\Clinic\Models\Appointment;
use App\Modules\Clinic\Models\Patient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ClinicDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Patient::query()->exists()) {
            return;
        }

        // A couple of service items to bill appointments against.
        $services = collect(['General consultation' => 60, 'Follow-up visit' => 35, 'Minor procedure' => 140])
            ->map(fn ($price, $name) => Item::firstOrCreate(
                ['sku' => 'SVC-'.strtoupper(substr(md5($name), 0, 6))],
                ['name' => $name, 'type' => 'service', 'unit_price' => $price, 'cost_price' => 0, 'is_active' => true],
            ))
            ->values();

        $doctors = ['Dr. Adeyemi', 'Dr. Novak', 'Dr. Salah'];

        $patients = collect(range(1, 12))->map(function () {
            $party = Party::factory()->customer()->create();

            return Patient::create([
                'party_id' => $party->id,
                'date_of_birth' => fake()->dateTimeBetween('-80 years', '-6 years')->format('Y-m-d'),
                'gender' => fake()->randomElement(['male', 'female']),
            ]);
        });

        foreach ($patients as $patient) {
            foreach (range(1, fake()->numberBetween(1, 3)) as $ignored) {
                $day = fake()->dateTimeBetween('-2 weeks', '+2 weeks');

                Appointment::create([
                    'patient_id' => $patient->id,
                    'doctor_name' => fake()->randomElement($doctors),
                    'starts_at' => Carbon::parse($day)->setTime(fake()->numberBetween(9, 16), fake()->randomElement([0, 30])),
                    'duration_minutes' => fake()->randomElement([20, 30, 45]),
                    'service_item_id' => fake()->boolean(80) ? $services->random()->id : null,
                    'status' => fake()->randomElement(['scheduled', 'scheduled', 'done', 'cancelled']),
                ]);
            }
        }
    }
}
