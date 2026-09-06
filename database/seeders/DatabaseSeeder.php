<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use App\Models\User;
use App\Support\ModuleRegistry;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $registry = app(ModuleRegistry::class);

        User::query()->firstOrCreate(
            ['email' => 'admin@erp.test'],
            ['name' => 'Owner', 'password' => 'password'],
        );

        if ($registry->isActive('clinic')) {
            User::query()->firstOrCreate(
                ['email' => 'reception@erp.test'],
                ['name' => 'Clinic Reception', 'password' => 'password'],
            );
        }

        if ($registry->isActive('clothing')) {
            User::query()->firstOrCreate(
                ['email' => 'shopfloor@erp.test'],
                ['name' => 'Shop Floor', 'password' => 'password'],
            );
        }

        CompanySetting::query()->firstOrCreate([], [
            'name' => match (true) {
                $registry->isActive('clinic') => 'Brookside Family Clinic',
                $registry->isActive('clothing') => 'Northgate Clothing Co.',
                default => 'Demo Trading Co.',
            },
            'address' => "12 Market Street\nCity Centre",
            'phone' => '+1 555 0100',
            'currency' => 'USD',
            'tax_rate' => 11.00,
            'sales_prefix' => 'INV',
            'purchase_prefix' => 'BILL',
        ]);

        $this->call(DemoDataSeeder::class);

        foreach ($registry->seeders() as $seeder) {
            $this->call($seeder);
        }
    }
}
