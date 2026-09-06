<?php

namespace App\Http\Controllers;

class SupplierController extends BasePartyController
{
    protected function role(): string
    {
        return 'supplier';
    }
}
