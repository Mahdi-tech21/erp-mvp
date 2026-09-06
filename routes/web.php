<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('customers', CustomerController::class)
    ->parameters(['customers' => 'party'])
    ->except('show');

Route::resource('suppliers', SupplierController::class)
    ->parameters(['suppliers' => 'party'])
    ->except('show');

Route::resource('items', ItemController::class)->except('show');
