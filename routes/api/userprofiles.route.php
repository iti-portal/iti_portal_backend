<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserProfileController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Advanced search and filtering
    Route::get('/user-profiles/search', [UserProfileController::class, 'searchAndFilter']);
    
   
});