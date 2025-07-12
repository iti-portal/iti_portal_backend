<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Chat\ConversationController;
use App\Http\Controllers\Chat\MessageController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('conversations', [ConversationController::class, 'index']);
    Route::post('conversations', [ConversationController::class, 'store']);
    Route::get('conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('messages', [MessageController::class, 'store']);
});
