<?php

namespace App\Http\Controllers;

class SalesInvoiceController extends BaseDocumentController
{
    protected function docType(): string
    {
        return 'sales_invoice';
    }
}
