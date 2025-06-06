<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// routes for user to managing their skills
Route::middleware([])->group(function(){
    //  this route for listing all skills of the user
    Route::get('/skills', function (Request $request) {
        return response()->json(['message' => 'List of user skills']);
    })->name('skills.list');
    // This route for adding a new skill
    Route::post('/skills', function (Request $request) {
        return response()->json(['message' => 'Skill added successfully']);
    })->name('skills.add');

    // This route for updating proficiency of existing skill 
    Route::put('/skills/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Skill with ID: $id updated successfully"]);
    })->name('skills.update');

    // This route for deleting a skill
    Route::delete('/skills/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Skill with ID: $id deleted successfully"]);
    })->name('skills.delete');
});

// routes for admin and staff to manage user skills
Route::middleware([])->group(function(){
    // this route for listing all skills
    Route::get('/skills/all', function (Request $request) {
        return response()->json(['message' => 'List of all skills']);
    })->name('skills.all');
    // this route for updating skill
    Route::put('/skills/{id}/admin', function (Request $request, $id) {
        return response()->json(['message' => "Skill with ID: $id updated by admin successfully"]);
    })->name('skills.admin.update');
    // this route for deleting skill
    Route::delete('/skills/{id}/admin', function (Request $request, $id) {
        return response()->json(['message' => "Skill with ID: $id deleted by admin successfully"]);
    })->name('skills.admin.delete');
    // this route for getting skill's categories
    Route::get('/skills/categories', function (Request $request) {
        return response()->json(['message' => 'List of skill categories']);
    })->name('skills.categories');
    // this route for delete skill's category and its skills
    Route::delete('/skills/categories/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Skill category with ID: $id and its skills deleted successfully"]);
    })->name('skills.categories.delete');
    // this route for updating skill's category
    Route::put('/skills/categories/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Skill category with ID: $id updated successfully"]);
    })->name('skills.categories.update');
    // this route for adding a new skill category
    Route::post('/skills/categories', function (Request $request) {
        return response()->json(['message' => 'Skill category added successfully']);
    })->name('skills.categories.add');
});

// routes  to manage education
Route::middleware([])->group(function(){
    // this route for listing all educations
    Route::get('/educations', function (Request $request) {
        return response()->json(['message' => 'List of all education details']);
    })->name('education.list');
    // This route for adding a new education detail
    Route::post('/education', function (Request $request) {
        return response()->json(['message' => 'Education detail added successfully']);
    })->name('education.add');
    // This route for updating an education detail
    Route::put('/education/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Education detail with ID: $id updated successfully"]);
    })->name('education.update');
    // This route for deleting an education detail
    Route::delete('/education/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Education detail with ID: $id deleted successfully"]);
    })->name('education.delete');
});