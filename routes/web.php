<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', \App\Http\Controllers\DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Parking Routes
    Route::get('/parking', [\App\Http\Controllers\ParkingController::class, 'index'])->name('parking.index');
    Route::post('/parking/entry', [\App\Http\Controllers\ParkingController::class, 'entry'])->name('parking.entry');
    Route::get('/parking/{ticket}/checkout', [\App\Http\Controllers\ParkingController::class, 'checkoutPreview'])->name('parking.checkoutPreview');
    Route::post('/parking/{ticket}/pay', [\App\Http\Controllers\ParkingController::class, 'pay'])->name('parking.pay');
    Route::post('/parking/{ticket}/cancel', [\App\Http\Controllers\ParkingController::class, 'cancel'])->name('parking.cancel');
    Route::get('/parking/{ticket}/receipt', [\App\Http\Controllers\ParkingController::class, 'receipt'])->name('parking.receipt');
    Route::get('/parking/report', [\App\Http\Controllers\ParkingController::class, 'report'])->name('parking.report');

    // Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::resource('vehicle-types', \App\Http\Controllers\Settings\VehicleTypeController::class)->except(['create', 'edit', 'show']);
        Route::resource('rates', \App\Http\Controllers\Settings\RateController::class)->except(['create', 'edit', 'show']);
    });
});

require __DIR__.'/auth.php';
