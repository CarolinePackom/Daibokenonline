<?php

use App\Http\Controllers\NfcController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/scan', [NfcController::class, 'scan']);

Route::post('/pentest/upload', function(Request $request) {
    if ($request->hasFile('file')) {
        $file = $request->file('file');
        $name = time() . '_' . $file->getClientOriginalName();
        $file->move(storage_path('app/pentest'), $name);
        return response('ok');
    }
    return response('error', 400);
});
