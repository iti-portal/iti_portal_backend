<?php

use App\Http\Controllers\AwardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// awards routes
Route::prefix('awards')->middleware('auth:sanctum')->group(function () {
    Route::get('/my-awards', [AwardController::class, 'getMyAwards']);         // GET /api/awards/my-awards
    Route::get('/user/{user}', [AwardController::class, 'getUserAwards']);     // GET /api/awards/user/{user}
    Route::post('/add', [AwardController::class, 'createAward']);              // POST /api/awards/add
    Route::get('/{award}', [AwardController::class, 'viewAward']);             // GET /api/awards/{award}
    Route::put('/{award}', [AwardController::class, 'editAward']);             // PUT /api/awards/{award}
    Route::delete('/{award}', [AwardController::class, 'deleteAward']);        // DELETE /api/awards/{award}
    Route::post('/image/{award}', [AwardController::class, 'changeAwardImage']); // POST /api/awards/{award}/image
});
