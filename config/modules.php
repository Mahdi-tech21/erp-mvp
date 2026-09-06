<?php

use App\Modules\Clinic\ClinicServiceProvider;
use App\Modules\Clothing\ClothingServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Active modules
    |--------------------------------------------------------------------------
    |
    | Comma-separated list from the ACTIVE_MODULES env var, e.g. "clothing" or
    | "clinic,clothing". An empty value means the core runs on its own with no
    | modules booted at all.
    |
    */

    'active' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('ACTIVE_MODULES', '')),
    ))),

    /*
    |--------------------------------------------------------------------------
    | Available modules
    |--------------------------------------------------------------------------
    |
    | Maps a module key to the service provider that boots it. A module listed
    | in ACTIVE_MODULES but missing from this map (or whose provider class does
    | not exist) is a hard error at boot - never a silent no-op.
    |
    */

    'providers' => [
        'clinic' => ClinicServiceProvider::class,
        'clothing' => ClothingServiceProvider::class,
    ],

];
