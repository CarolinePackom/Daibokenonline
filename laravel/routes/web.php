<?php

use App\Http\Controllers\NfcController;
use Illuminate\Support\Facades\Route;

Route::get('/welcome', function () {
    return view('welcome');
});

Route::group(['prefix' => 'nfc'], function () {
    Route::get('/dernier-client', [NfcController::class, 'recupererDernierClient'])->name('nfc.recupererDernierClient');
    Route::get('/dernier-id', [NfcController::class, 'recupererDernierIdNfc'])->name('nfc.recupererDernierIdNfc');
});
