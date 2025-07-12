<?php

use App\Http\Controllers\WorkExperienceController;
use Illuminate\Support\Facades\Route;

// Routes for authenticated users with role: student or alumni

Route::middleware(['auth:sanctum','role:student|alumni'])->group(function () {
   // Retrieve all work experiences for the authenticated user
    Route::get('/user-work-experiences', [WorkExperienceController::class, 'index']);

    // Store a new work experience for the authenticated user
    Route::post('/user-work-experiences', [WorkExperienceController::class, 'store']);

   // Update an existing work experience
    Route::put('/user-work-experiences/{workExperience}', [WorkExperienceController::class, 'update']);

    // Delete a work experience
    
    Route::delete('/user-work-experiences/{workExperience}', [WorkExperienceController::class, 'destroy']);
});

// Public route to view a specific user's work experiences
Route::get('/users/{user}/work-experiences', [WorkExperienceController::class, 'showUserExperiences'])
->name('users.work-experiences.show')->middleware('auth:sanctum');
