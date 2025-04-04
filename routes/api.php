<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;

Route::middleware('verified')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
});

Route::get("/verify-email/{id}/{hash}", VerifyEmailController::class)
    ->name("verification.verify");
