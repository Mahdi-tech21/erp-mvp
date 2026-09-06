<?php

namespace App\Http\Controllers;

class PurchaseInvoiceController extends BaseDocumentController
{
    protected function docType(): string
    {
        return 'purchase_invoice';
    }
}
