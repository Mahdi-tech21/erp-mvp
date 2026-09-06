<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'admin@erp.test'],
            ['name' => 'Admin', 'password' => 'password'],
        );

        CompanySetting::query()->firstOrCreate([], [
            'name' => 'Demo Trading Co.',
            'address' => "12 Market Street\nCity Centre",
            'phone' => '+1 555 0100',
            'currency' => 'USD',
            'tax_rate' => 11.00,
            'sales_prefix' => 'INV',
            'purchase_prefix' => 'BILL',
        ]);

        $this->call(DemoDataSeeder::class);
    }
}
