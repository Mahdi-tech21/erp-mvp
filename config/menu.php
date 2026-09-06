<?php

/*
|--------------------------------------------------------------------------
| Core sidebar menu
|--------------------------------------------------------------------------
|
| The core navigation, grouped into sections. Each item names a route; the
| sidebar only renders items whose route actually exists yet (Route::has),
| so this file can list the whole core up front without creating dead links
| as the build progresses. Modules add their own items via
| App\Support\ModuleRegistry::addMenuItem().
|
*/

return [

    'sections' => [

        [
            'label' => null,
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard'],
            ],
        ],

        [
            'label' => 'Sales',
            'items' => [
                ['label' => 'Customers', 'route' => 'customers.index', 'icon' => 'customers'],
                ['label' => 'Invoices', 'route' => 'sales-invoices.index', 'icon' => 'invoices'],
            ],
        ],

        [
            'label' => 'Purchases',
            'items' => [
                ['label' => 'Suppliers', 'route' => 'suppliers.index', 'icon' => 'suppliers'],
                ['label' => 'Bills', 'route' => 'purchase-invoices.index', 'icon' => 'bills'],
            ],
        ],

        [
            'label' => 'Catalogue',
            'items' => [
                ['label' => 'Items', 'route' => 'items.index', 'icon' => 'items'],
            ],
        ],

        [
            'label' => 'Money',
            'items' => [
                ['label' => 'Payments', 'route' => 'payments.index', 'icon' => 'payments'],
                ['label' => 'Expenses', 'route' => 'expenses.index', 'icon' => 'expenses'],
            ],
        ],

        [
            'label' => 'Reports',
            'items' => [
                ['label' => 'Reports', 'route' => 'reports.index', 'icon' => 'reports'],
            ],
        ],

        [
            'label' => 'System',
            'items' => [
                ['label' => 'Audit log', 'route' => 'audit.index', 'icon' => 'audit'],
            ],
        ],

    ],

];
