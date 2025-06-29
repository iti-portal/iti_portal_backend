<?php

use App\Http\Controllers\AchievementController;
use App\Http\Controllers\AchievementLikeController;
use App\Models\AchievementLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function(){
    // this route for listing all achievements
    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.list');

    // route for getting user's achievements
    Route::get('/my-achievements', [AchievementController::class, 'userAchievements'])->name('myachievments.list');


    // route for getting connections achievements
    Route::get('/connections-achievements', [AchievementController::class, 'userConnectionsAchievements'])
    ->name('list.connections.achievements');


    // route for getting popular achievements
    Route::get('/popular-achievements', [AchievementController::class, 'popularAchievements'])
    ->name('list.popular.achievements');


    // This route for adding a new achievement
    Route::post('/achievements', [AchievementController::class, 'store'])->name('achievements.add');


    // route for updating an achievement
    Route::put('/achievements/{achievement}', [AchievementController::class, ''])->name('achievements.update');

    // route for deleting an achievement
    Route::delete('/achievements/{achievement}', [AchievementController::class, 'destroy'])->name('achievements.delete');
   
  

    // route for like and unlike an achievement
    Route::post('/achievements/like', [AchievementLikeController::class, 'toggleLike'])->name('achievements.like');

    

    // route for comment on an achievement
    Route::post('/achievements/{achievement}/comment', function (Request $request, $id) {
        return response()->json(['message' => "Comment added on achievement with ID: $id successfully"]);
    })->name('achievements.comment');

});
