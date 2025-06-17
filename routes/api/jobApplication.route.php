<?php

use App\Http\Controllers\JobApplicationController;
use Illuminate\Support\Facades\Route;

// Job application routes - all routes require authentication
Route::middleware(['auth:sanctum'])->group(function () {
    // For students and alumni
    Route::middleware(['role:student|alumni'])->group(function () {
        Route::get('/my-applications', [JobApplicationController::class, 'index']);
        Route::post('/job-applications', [JobApplicationController::class, 'store']);
        Route::get('/job-applications/{id}', [JobApplicationController::class, 'show']);
        Route::put('/job-applications/{id}/update-cv', [JobApplicationController::class, 'updateCV']);
        Route::delete('/job-applications/{id}', [JobApplicationController::class, 'destroy']);
    });

    // For companies
    Route::middleware(['role:company'])->group(function () {
        Route::get('/company/applications', [JobApplicationController::class, 'companyApplications']);
        Route::patch('/company/applications/{id}/status', [JobApplicationController::class, 'updateStatus']);
    });

    // Download CV route - accessible by both students/alumni and companies
    // The downloadCV method has its own role checks internally
    Route::get('/job-applications/{id}/download-cv', [JobApplicationController::class, 'downloadCV']);
});
