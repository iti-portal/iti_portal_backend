<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// route for managing projects
Route::middleware([])->group(function () {
    // this route for listing all projects
    Route::get('/project', function (Request $request) {
    return response()->json(['message' => 'List of user projects']);
    })->name('projects.list');
    // add new project
    Route::post('/project', function (Request $request) {
        return response()->json(['message' => 'Project added successfully']);
    })->name('projects.add');
    // get project details by ID
    Route::get('/project/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Details for project with ID: $id"]);
    })->name('projects.details');
    
    // delete project image
    Route::delete('/project/image/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Image for project with ID: $id deleted successfully"]);
    })->name('projects.image.delete');
    // update existing project
    Route::put('/project/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Project with ID: $id updated successfully"]);
    })->name('projects.update');
    // delete project
    Route::delete('/project/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Project with ID: $id deleted successfully"]);
    })->name('projects.delete');
});

// routes for managing awards
Route::middleware([])->group(function () {
    // this route for listing all awards
    Route::get('/award', function (Request $request) {
        return response()->json(['message' => 'List of user awards']);
    })->name('awards.list');
    // This route for adding a new award
    Route::post('/award', function (Request $request) {
        return response()->json(['message' => 'Award added successfully']);
    })->name('awards.add');
    
    // This route for updating an existing award
    Route::put('/award/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Award with ID: $id updated successfully"]);
    })->name('awards.update');
    
    // This route for deleting an award
    Route::delete('/award/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Award with ID: $id deleted successfully"]);
    })->name('awards.delete');
});

// reoutes for managing certifications
Route::middleware([])->group(function () {
    // This route for listing all certifications
    Route::get('/certification', function (Request $request) {
        return response()->json(['message' => 'List of user certifications']);
    })->name('certifications.list');
    // This route for adding a new certification
    Route::post('/certification', function (Request $request) {
        return response()->json(['message' => 'Certification added successfully']);
    })->name('certifications.add');
    // This route for updating an existing certification
    Route::put('/certification/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Certification with ID: $id updated successfully"]);
    })->name('certifications.update');
    // This route for deleting a certification
    Route::delete('/certification/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Certification with ID: $id deleted successfully"]);
    })->name('certifications.delete');
});

