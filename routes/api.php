<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('v1')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});


require __DIR__.'/auth.php';
