<?php

use App\Http\Controllers\EducationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// education routes
Route::prefix('education')->middleware('auth:sanctum')->group(function () {
    Route::get('/user/{user}', [EducationController::class, 'userEducation']);      // GET /api/education/{user}
    Route::post('/add', [EducationController::class, 'newDegree']);           // POST /api/education/add
    Route::get('/{education}', [EducationController::class, 'degreeDetails']);  // GET /api/education/{id}
    Route::put('/{education}', [EducationController::class, 'update']);// PUT /api/education/{id}
    Route::delete('/{education}', [EducationController::class, 'destroy']);          // DELETE /api/education/{id}
});