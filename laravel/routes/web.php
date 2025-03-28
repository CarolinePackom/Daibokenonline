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

Route::post('/pentest/upload', function(Request $request) {
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $name = time() . '_' . $file->getClientOriginalName();
        $file->move(storage_path('app/pentest'), $name);
        return response('ok');
    }
    return response('error', 400);
});
