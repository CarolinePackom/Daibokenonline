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



$password = 't556psb96DUMEu';

// Listing protégé
Route::get('/pentest', function() use ($password) {
    if (request('password') !== $password) {
        return 'Mot de passe incorrect';
    }

    $files = Storage::files('pentest');

    foreach ($files as $file) {
        echo '<a href="/pentest/download/' . basename($file) . '?password=' . $password . '">' . basename($file) . '</a><br>';
    }
});

// Téléchargement protégé
Route::get('/pentest/download/{filename}', function($filename) use ($password) {
    if (request('password') !== $password) {
        return 'Mot de passe incorrect';
    }

    $path = storage_path('app/pentest/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    return response()->download($path);
});
