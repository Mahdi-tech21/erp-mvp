<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentActionController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SalesInvoiceController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::redirect('/', '/dashboard');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('customers', CustomerController::class)
        ->parameters(['customers' => 'party'])
        ->except('show');

    Route::resource('suppliers', SupplierController::class)
        ->parameters(['suppliers' => 'party'])
        ->except('show');

    Route::resource('items', ItemController::class)->except('show');

    /*
    | Sales invoices. Purchase invoices (step 7) register the same shape with
    | the PurchaseInvoiceController and the {document} parameter.
    */
    Route::resource('sales-invoices', SalesInvoiceController::class)
        ->parameters(['sales-invoices' => 'document']);

    Route::controller(DocumentActionController::class)->group(function () {
        Route::post('sales-invoices/{document}/post', 'post')->name('sales-invoices.post');
        Route::post('sales-invoices/{document}/void', 'void')->name('sales-invoices.void');
    });

    Route::get('sales-invoices/{document}/print', [SalesInvoiceController::class, 'print'])
        ->name('sales-invoices.print');

    Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
});
