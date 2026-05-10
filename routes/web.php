<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/reservations', [ReservationController::class, 'store'])
    ->name('reservations.store');
Route::get('/occasions', [ReservationController::class, 'occasions'])
    ->name('reservations.occasions');
Route::get('/reservation-addons', [ReservationController::class, 'reservationAddons'])
    ->name('reservations.addons');
Route::get('/dietary-options', [ReservationController::class, 'dietaryOptions'])
    ->name('reservations.dietary-options');
Route::get('/availability', [ReservationController::class, 'availability'])
    ->name('reservations.availability');

Route::get('/panel-locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['ar', 'en'], true), 404);

    session(['panel_locale' => $locale]);
    app()->setLocale($locale);

    return redirect()->back();
})->name('panel.locale.switch');
