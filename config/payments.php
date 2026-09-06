<?php

/*
|--------------------------------------------------------------------------
| Payment direction labels
|--------------------------------------------------------------------------
|
| `payments` is one table. Money in (from a customer, against sales
| invoices) and money out (to a supplier, against bills) are the same
| screens with the wording swapped from this map.
|
*/

return [

    'directions' => [

        'in' => [
            'noun' => 'Customer payment',
            'action' => 'Record customer payment',
            'party_singular' => 'Customer',
            'party_flag' => 'is_customer',
            'doc_type' => 'sales_invoice',
            'doc_noun' => 'invoice',
        ],

        'out' => [
            'noun' => 'Supplier payment',
            'action' => 'Record supplier payment',
            'party_singular' => 'Supplier',
            'party_flag' => 'is_supplier',
            'doc_type' => 'purchase_invoice',
            'doc_noun' => 'bill',
        ],

    ],

    'methods' => ['cash', 'card', 'transfer', 'cheque'],

];
