<?php

/*
|--------------------------------------------------------------------------
| Document type labels
|--------------------------------------------------------------------------
|
| `documents` is one table. A sales invoice and a purchase invoice are the
| same screens with the wording swapped from this map. The controller and
| the Blade views never say "Invoice" or "Supplier" literally.
|
*/

return [

    'types' => [

        'sales_invoice' => [
            'doc_singular' => 'Invoice',
            'doc_plural' => 'Invoices',
            'party_singular' => 'Customer',
            'party_flag' => 'is_customer',
            'route' => 'sales-invoices',
            'direction' => 'in',
            'has_external_ref' => false,
        ],

        'purchase_invoice' => [
            'doc_singular' => 'Bill',
            'doc_plural' => 'Bills',
            'party_singular' => 'Supplier',
            'party_flag' => 'is_supplier',
            'route' => 'purchase-invoices',
            'direction' => 'out',
            'has_external_ref' => true,
        ],

    ],

];
