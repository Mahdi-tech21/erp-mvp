<?php

use App\Modules\Clothing\Http\Controllers\StockAdjustmentController;
use App\Modules\Clothing\Http\Controllers\VariantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('clothing')->name('clothing.')->group(function () {
    Route::get('stock', [VariantController::class, 'index'])->name('stock.index');
    Route::get('stock/new', [VariantController::class, 'create'])->name('stock.create');
    Route::post('stock', [VariantController::class, 'store'])->name('stock.store');
    Route::get('stock/{variant}/edit', [VariantController::class, 'edit'])->name('stock.edit');
    Route::put('stock/{variant}', [VariantController::class, 'update'])->name('stock.update');

    Route::post('stock/{variant}/adjust', StockAdjustmentController::class)->name('stock.adjust');
});
