<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentActionController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseInvoiceController;
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
    | Sales invoices and purchase invoices are the same set of routes with the
    | words swapped - one shape, two named children of BaseDocumentController.
    */
    $documentRoutes = function (string $prefix, string $controller) {
        Route::resource($prefix, $controller)->parameters([$prefix => 'document']);
        Route::post("{$prefix}/{document}/post", [DocumentActionController::class, 'post'])->name("{$prefix}.post");
        Route::post("{$prefix}/{document}/void", [DocumentActionController::class, 'void'])->name("{$prefix}.void");
        Route::get("{$prefix}/{document}/print", [$controller, 'print'])->name("{$prefix}.print");
    };

    $documentRoutes('sales-invoices', SalesInvoiceController::class);
    $documentRoutes('purchase-invoices', PurchaseInvoiceController::class);

    Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');
});
