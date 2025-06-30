<?php

use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\Admin\JobApplicationController as AdminJobApplicationController;
use Illuminate\Support\Facades\Route;

// Job application routes - all routes require authentication
Route::middleware(['auth:sanctum'])->group(function () {
    // For students and alumni
    Route::middleware(['role:student|alumni'])->group(function () {
        Route::get('/my-applications', [JobApplicationController::class, 'index']);
        Route::post('/job-applications', [JobApplicationController::class, 'store']);
        Route::get('/job-applications/{id}', [JobApplicationController::class, 'show']);
        Route::delete('/job-applications/{id}', [JobApplicationController::class, 'destroy']); // Withdraw application
        
        // Skill matching for users
        Route::get('/jobs/{jobId}/skill-match', [JobApplicationController::class, 'getJobSkillMatch']);
    });

    // For companies
    Route::middleware(['role:company'])->group(function () {
        Route::get('/company/applications', [JobApplicationController::class, 'companyApplications']);
        Route::patch('/company/applications/{id}/status', [JobApplicationController::class, 'updateStatus']);
        Route::patch('/company/applications/{id}/hire', [JobApplicationController::class, 'hire']);
        Route::patch('/company/applications/{id}/reject', [JobApplicationController::class, 'reject']);
        Route::patch('/company/applications/{id}/interview', [JobApplicationController::class, 'interview']);
        Route::put('/company/applications/batch-update-status', [JobApplicationController::class, 'batchUpdateStatus']);
        Route::post('/applications/{id}/track-profile-view', [JobApplicationController::class, 'trackProfileView']);
        
        // Skill matching and analytics for companies
        Route::get('/jobs/{jobId}/applications/matched', [JobApplicationController::class, 'getMatchedApplications']);
        Route::get('/jobs/{jobId}/applications/stats', [JobApplicationController::class, 'getJobApplicationStats']);
    });

    // Admin/Staff routes
    Route::middleware(['role:admin|staff'])->prefix('admin')->group(function () {
        Route::get('/applications/statistics', [AdminJobApplicationController::class, 'statistics']);
        Route::get('/applications', [AdminJobApplicationController::class, 'index']);
        Route::get('/applications/{id}', [AdminJobApplicationController::class, 'show']);
        Route::patch('/applications/{id}/status', [AdminJobApplicationController::class, 'updateStatus']);
        Route::delete('/applications/{id}', [AdminJobApplicationController::class, 'destroy']);
    });

    // Download CV route - accessible by students/alumni, companies, and admin/staff
    // The downloadCV method has its own role checks internally
    Route::get('/job-applications/{id}/download-cv', [JobApplicationController::class, 'downloadCV']);
});
