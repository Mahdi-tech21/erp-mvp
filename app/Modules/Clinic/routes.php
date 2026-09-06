<?php

use App\Modules\Clinic\Http\Controllers\AppointmentController;
use App\Modules\Clinic\Http\Controllers\AppointmentInvoiceController;
use App\Modules\Clinic\Http\Controllers\PatientController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('clinic')->name('clinic.')->group(function () {
    Route::resource('patients', PatientController::class)->except('show');

    Route::resource('appointments', AppointmentController::class)->except('show');
    Route::post('appointments/{appointment}/done', [AppointmentController::class, 'markDone'])->name('appointments.done');
    Route::post('appointments/{appointment}/invoice', AppointmentInvoiceController::class)->name('appointments.invoice');
});
