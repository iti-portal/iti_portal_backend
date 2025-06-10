<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// manage connections between users
Route::middleware([])->group(function () {
    // this route for listing all connections of a user
    Route::get('/connections', function (Request $request) {
        return response()->json(['message' => 'List of user connections']);
    })->name('connections.list');
    // this route for sending a connection request
    Route::post('/connections', function (Request $request) {
        return response()->json(['message' => 'Connection request sent successfully']);
    })->name('connections.send.request');
    // this route for accepting a connection request
    Route::put('/connections/{id}/accept', function (Request $request, $id) {
        return response()->json(['message' => "Connection request with ID: $id accepted successfully"]);
    })->name('connections.accept.request');
    // this route for rejecting a connection request
    Route::put('/connections/{id}/reject', function (Request $request, $id) {
        return response()->json(['message' => "Connection request with ID: $id rejected successfully"]);
    })->name('connections.reject.request');
    // route for listing connection requests
    Route::get('/connections/requests', function (Request $request) {
        return response()->json(['message' => 'List of connection requests']);
    });
});