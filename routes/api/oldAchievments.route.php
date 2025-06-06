<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// route for managing projects
Route::middleware([])->get('/achievements', function (Request $request) {
    // this route for listing all projects
    Route::get('/projects', function (Request $request) {
    return response()->json(['message' => 'List of user projects']);
})->name('projects.list');
    // add new project
    Route::post('/projects', function (Request $request) {
        return response()->json(['message' => 'Project added successfully']);
    })->name('projects.add');
    // get project details by ID
    Route::get('/projects/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Details for project with ID: $id"]);
    })->name('projects.details');
    // add project image
    Route::post('/projects/{id}/image', function (Request $request, $id) {
        return response()->json(['message' => "Image for project with ID: $id added successfully"]);
    })->name('projects.image.add');
    // delete project image
    Route::delete('/projects/image/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Image for project with ID: $id deleted successfully"]);
    })->name('projects.image.delete');
    // update existing project
    Route::put('/projects/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Project with ID: $id updated successfully"]);
    })->name('projects.update');
    // delete project
    Route::delete('/projects/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Project with ID: $id deleted successfully"]);
    })->name('projects.delete');
});

// routes for managing awards
Route::middleware([])->group(function () {
    // this route for listing all awards
    Route::get('/awards', function (Request $request) {
        return response()->json(['message' => 'List of user awards']);
    })->name('awards.list');
    // This route for adding a new award
    Route::post('/awards', function (Request $request) {
        return response()->json(['message' => 'Award added successfully']);
    })->name('awards.add');
    
    // This route for updating an existing award
    Route::put('/awards/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Award with ID: $id updated successfully"]);
    })->name('awards.update');
    
    // This route for deleting an award
    Route::delete('/awards/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Award with ID: $id deleted successfully"]);
    })->name('awards.delete');
});

// reoutes for managing certifications
Route::middleware([])->group(function () {
    // This route for listing all certifications
    Route::get('/certifications', function (Request $request) {
        return response()->json(['message' => 'List of user certifications']);
    })->name('certifications.list');
    // This route for adding a new certification
    Route::post('/certifications', function (Request $request) {
        return response()->json(['message' => 'Certification added successfully']);
    })->name('certifications.add');
    // This route for updating an existing certification
    Route::put('/certifications/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Certification with ID: $id updated successfully"]);
    })->name('certifications.update');
    // This route for deleting a certification
    Route::delete('/certifications/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Certification with ID: $id deleted successfully"]);
    })->name('certifications.delete');
});

