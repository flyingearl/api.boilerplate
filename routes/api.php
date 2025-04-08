<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;

Route::middleware('verified')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });

    Route::controller(ProfileController::class)->group(function () {
        Route::post('/profile', 'update');
    });
});

Route::get("/verify-email/{id}/{hash}", VerifyEmailController::class)
    ->name("verification.verify");
