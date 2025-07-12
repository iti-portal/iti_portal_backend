<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegistrationController;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::post('auth/register', [RegistrationController::class, 'registerIndividual']);
Route::post('auth/register-company', [RegistrationController::class, 'registerCompany']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/external-login', [AuthController::class, 'externalLogin'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('external.login');

// Email verification routes
Route::get('auth/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

// Reset password routes
Route::prefix('auth')->middleware('guest')->group(function () {
    Route::post('forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('password.request');
    Route::post('reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
});

// Sanctum only routes (authenticated but allow unverified)
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('resend-verification', [AuthController::class, 'resendVerificationEmail'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::post('logout', [AuthController::class, 'logout']);
});


// Protected user route (requires full approval)
Route::middleware(['auth:sanctum', 'account.approved'])->group(function () {
    Route::get('auth/user', [AuthController::class, 'user']);
});