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

Route::get('/pentest', function() {
    $files = Storage::files('pentest');

    foreach ($files as $file) {
        echo '<a href="/pentest/download/' . basename($file) . '">' . basename($file) . '</a><br>';
    }
});

Route::get('/pentest/download/{filename}', function($filename) {
    $path = storage_path('app/pentest/' . $filename);
    if (!File::exists($path)) {
        abort(404);
    }
    return response()->download($path);
});
