<?php

use App\Http\Controllers\SkillController;
use Illuminate\Support\Facades\Route;

// Custom skill management routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Get all skills for a specific user
    Route::get('/user-skills/{id}', [SkillController::class, 'getAllSkillsForUser']);
    
    // Add a skill (with skill_id or skill_name)
    Route::post('/user-skills', [SkillController::class, 'addSkill']);
    
    // Delete a user skill
    Route::delete('/user-skills/{id}', [SkillController::class, 'deleteSkill']);
});