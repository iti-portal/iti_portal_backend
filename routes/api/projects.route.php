<?php

use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    // Project routes
    Route::get('/projects/{user}', [ProjectController::class, 'getUserProjects']);
    Route::get('/projects/{user}/featured', [ProjectController::class, 'getUserFeaturedProjects']);
    Route::post('/projects/new-project', [ProjectController::class, 'createProject']);
    Route::put('/projects/{project}', [ProjectController::class, 'editProject']);
    Route::delete('/projects/{project}', [ProjectController::class, 'deleteProject']);
    
    // Project image routes
    Route::post('/projects/{project}/images', [ProjectController::class, 'addImage']);
    Route::delete('/projects/images/{projectImage}', [ProjectController::class, 'deleteImage']);
    Route::put('/projects/images/order', [ProjectController::class, 'updateImageOrder']);
});