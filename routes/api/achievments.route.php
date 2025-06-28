<?php

use App\Http\Controllers\AchievementController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function(){
    // this route for listing all achievements
    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.list');

    // route for getting user's achievements
    Route::get('/myachievements', [AchievementController::class, 'userAchievements'])->name('myachievments.list');


    // This route for adding a new achievement
    Route::post('/achievements', [AchievementController::class, 'store'])->name('achievements.add');
   
    // // route for getting achievement details by ID
    // Route::get('/achievements/{id}', function (Request $request, $id) {
    //     return response()->json(['message' => "Details for achievement with ID: $id"]);
    // })->name('achievements.details');
    // route for getting all achievements of a user
    // Route::get('/achievements/itian/{userId}', function (Request $request, $userId) {
    //     return response()->json(['message' => "List of achievements for user with ID: $userId"]);
    // })->name('achievements.itian');

    // route for like an achievement
    Route::post('/achievements/{id}/like', function (Request $request, $id) {
        return response()->json(['message' => "Achievement with ID: $id liked successfully"]);
    })->name('achievements.like');
    // route for comment on an achievement
    Route::post('/achievements/{id}/comment', function (Request $request, $id) {
        return response()->json(['message' => "Comment added on achievement with ID: $id successfully"]);
    })->name('achievements.comment');

});
// route for managing achievements
Route::middleware([])->group(function(){
     // This route for updating an existing achievement
     Route::put('/achievements/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Achievement with ID: $id updated successfully"]);
    })->name('achievements.update');
    // This route for deleting an achievement
    Route::delete('/achievements/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Achievement with ID: $id deleted successfully"]);
    })->name('achievements.delete');

    // route for cancel like on an achievement
    Route::delete('/achievements/like/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Like on achievement cancelled successfully"]);
    })->name('achievements.cancel.like');

    // route for deleting comment on an achievement
    Route::delete('/achievements/comment/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Comment on achievement with ID: $id deleted successfully"]);
    })->name('achievements.delete.comment');
    // route for updating comment on an achievement
    // Route::put('/achievements/comment/{id}', function (Request $request, $id) {
    //     return response()->json(['message' => "Comment on achievement with ID: $id updated successfully"]);
    // })->name('achievements.update.comment');
});