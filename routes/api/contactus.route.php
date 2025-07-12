<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactUsController;

Route::prefix('contact-us')->group(function () {
    // Public route with rate limiting
    Route::post('/', [ContactUsController::class, 'store'])->middleware('throttle:5,1'); // 5 requests per minute

    // Admin and staff only routes
    Route::middleware(['auth:sanctum', 'role:admin|staff'])->group(function () {
        Route::get('/', [ContactUsController::class, 'index']);
        Route::get('/{id}', [ContactUsController::class, 'show']);
        Route::delete('/{id}', [ContactUsController::class, 'destroy']);
    });
});
