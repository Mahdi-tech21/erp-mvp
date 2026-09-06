<?php

namespace App\Modules\Clinic;

use App\Support\ModuleRegistry;
use Illuminate\Support\ServiceProvider;

class ClinicServiceProvider extends ServiceProvider
{
    public function boot(ModuleRegistry $registry): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'clinic');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $registry->addMenuItem('Patients', 'clinic.patients.index', 'Clinic', 'stethoscope', 10);
        $registry->addMenuItem('Appointments', 'clinic.appointments.index', 'Clinic', 'calendar', 20);

        $registry->addSeeder(ClinicDemoSeeder::class);
    }
}
