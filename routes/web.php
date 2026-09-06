<?php

use App\Http\Controllers\AssistantController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentActionController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\ReportController;
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

    Route::resource('expenses', ExpenseController::class)->except('show');

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

    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');

        foreach (['in', 'out'] as $direction) {
            Route::get("{$direction}/new", [PaymentController::class, 'create'])
                ->defaults('direction', $direction)->name("{$direction}.create");
            Route::post($direction, [PaymentController::class, 'store'])
                ->defaults('direction', $direction)->name("{$direction}.store");
        }

        Route::get('{payment}', [PaymentController::class, 'show'])->name('show')->whereNumber('payment');
    });

    Route::prefix('reports')->name('reports.')->controller(ReportController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('sales', 'sales')->name('sales');
        Route::get('purchases', 'purchases')->name('purchases');
        Route::get('ar-aging', 'arAging')->name('ar-aging');
        Route::get('ap-aging', 'apAging')->name('ap-aging');
        Route::get('statement', 'statement')->name('statement');
        Route::get('vat-return', 'vatReturn')->name('vat-return');
        Route::get('margin', 'margin')->name('margin');
    });

    Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');

    Route::prefix('assistant')->name('assistant.')->group(function () {
        Route::get('history', [AssistantController::class, 'history'])->name('history');
        Route::post('message', [AssistantController::class, 'message'])
            ->middleware('throttle:20,1')
            ->name('message');
    });
});
