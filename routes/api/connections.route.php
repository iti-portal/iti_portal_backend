<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ConnectionController;

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Connection management routes
    Route::prefix('connections')->group(function () {
        // Send connection request
        Route::post('/connect', [ConnectionController::class, 'connect']);
        // Accept connection request
        Route::put('/accept', [ConnectionController::class, 'acceptConnection']);
        // Reject/Decline connection request
        Route::put('/reject', [ConnectionController::class, 'rejectConnection']);
        // Remove/Disconnect from existing connection
        Route::delete('/disconnect', [ConnectionController::class, 'unconnect']);
        // Get connected users (accepted connections)
        Route::get('/connected', [ConnectionController::class, 'getConnectedUsers']);
        // Get pending connection requests (received)
        Route::get('/pending', [ConnectionController::class, 'getPendingRequests']);
        // Get sent connection requests
        Route::get('/sent', [ConnectionController::class, 'getSentRequests']);
        // Get connection status with specific user
        Route::get('/status', [ConnectionController::class, 'getConnectionStatus']);
    });
});