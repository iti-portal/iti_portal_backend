<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// adding service and manage it for alunmini
Route::middleware([])->group(function () {
    // this route for listing all user services
    Route::get('/service', function (Request $request) {
        return response()->json(['message' => 'List of user services']);
    })->name('services.list');
    // This route for adding a new service
    Route::post('/service', function (Request $request) {
        return response()->json(['message' => 'Service added successfully']);
    })->name('services.add');
    // route for update existing service
    Route::put('/service/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Service with ID: $id updated successfully"]);
    })->name('services.update');
    // route for delete service
    Route::delete('/service/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Service with ID: $id deleted successfully"]);
    })->name('services.delete');
});

// route for managing service by admin and staff
Route::middleware([])->group(function () {
    // this route for listing all services
    Route::get('/services/all', function (Request $request) {
        return response()->json(['message' => 'List of all services']);
    })->name('services.all');
    // this route for updating service
    Route::put('/manage-service/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Service with ID: $id updated by admin successfully"]);
    })->name('services.admin.update');
    //route for deleting service can be as for alumini above

});