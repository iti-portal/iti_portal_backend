<?php

use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;

// Custom skill management routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Get all skills for a specific user
    Route::get('/user-skills/{id}', [SkillController::class, 'getAllSkillsForUser']);
    
    // Add a skill
    Route::post('/user-skills', [SkillController::class, 'addSkill']);
    
    // Delete a user skill (now using URL parameter)
    Route::delete('/user-skills/{id}', [SkillController::class, 'deleteSkill']);

    // Get all skills from the database
    Route::get('/skills', [SkillController::class, 'getAllSkills']);

    // Search for skills
    Route::get('/skills/search', [SkillController::class, 'searchSkills']);
});
