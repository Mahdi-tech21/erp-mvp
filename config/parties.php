<?php

/*
|--------------------------------------------------------------------------
| Party role labels
|--------------------------------------------------------------------------
|
| `parties` is one table. The Customer and Supplier screens are the same
| CRUD filtered on a flag, with the wording swapped from this map - never
| hardcoded in a controller or a Blade view.
|
*/

return [

    'roles' => [

        'customer' => [
            'flag' => 'is_customer',
            'singular' => 'Customer',
            'plural' => 'Customers',
            'route' => 'customers',
        ],

        'supplier' => [
            'flag' => 'is_supplier',
            'singular' => 'Supplier',
            'plural' => 'Suppliers',
            'route' => 'suppliers',
        ],

    ],

];
