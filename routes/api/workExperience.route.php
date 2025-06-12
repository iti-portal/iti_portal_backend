<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkExperienceController;


// Custom work experience management routes

Route::middleware(['auth:sanctum'] )->group(function () {
    // Get all work experiences for a specific user
    Route::get('/user-work-experiences', [WorkExperienceController::class, 'index']);

    // Add a work experience
    Route::post('/user-work-experiences', [WorkExperienceController::class, 'store']);

    // Update a work experience
    Route::put('/user-work-experiences/{workExperience}', [WorkExperienceController::class, 'update']);

    // Delete a work experience
    Route::delete('/user-work-experiences/{workExperience}', [WorkExperienceController::class, 'destroy']);
});
