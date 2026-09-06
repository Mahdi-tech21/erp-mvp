<?php

namespace App\Http\Controllers;

class CustomerController extends BasePartyController
{
    protected function role(): string
    {
        return 'customer';
    }
}
