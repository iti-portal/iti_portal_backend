<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// display and management user details
Route::middleware([])->group(function () {
    // Routes for current user
    // This route for getting user profile
    Route::get('/profile', fn(Request $request)
        => response()->json(['message' => 'This is the profile route']))
        ->name('profile');
    // This route for updating user profile
    Route::put('/profile', fn (Request $request)=>
        response()->json(['message' => 'Profile updated successfully'])
    )->name('profile.update');
    // This route for deleting user profile
    Route::delete('/profile', fn (Request $request)=>
        response()->json(['message' => 'Profile deleted successfully'])
    )->name('profile.delete');
    
    // This route for getting user profile details by ID
    Route::get('/profile/{id}', fn(Request $request, $id) 
        => response()->json(['message' => "Profile details for user with ID: $id"]))
        ->name('profile.details');
    // This route for listing all users
    Route::get('/itians', function (Request $request) {
        return response()->json(['message' => 'List of ITIans']);
    })->name('itians.list');
    // this route for listing all staff members
    Route::get('/staff', function (Request $request) {
        return response()->json(['message' => 'List of staff members']);
    })->name('staff.list');
    });
    
    // routes for user's management by admin and staff
    Route::middleware([])->group(function () {
//    route for suspending a user
    Route::post('/suspend-user/{id}', function (Request $request, $id) {
        return response()->json(['message' => "User with ID: $id suspended successfully"]);
    })->name('users.suspend');
    
    // This route for deleting a user
    Route::delete('/users/{id}', function (Request $request, $id) {
        return response()->json(['message' => "User with ID: $id deleted successfully"]);
    })->name('users.delete');
    // This route for getting user details by ID we can use the same controller in profile.details route above
    Route::get('/users/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Details for user with ID: $id"]);
    })->name('users.details');
    });
    
    
    // routes for managing staff members by admin
    Route::middleware([])->group(function () {
    // this route for listing all staff members
    Route::get('/staff', function (Request $request) {
        return response()->json(['message' => 'List of all staff members']);
    })->name('staff.list');
    // This route for deleting a staff member
    Route::delete('/staff/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Staff member with ID: $id deleted successfully"]);
    })->name('staff.delete');
    // This route for getting staff member details by ID
    Route::get('/staff/{id}', function (Request $request, $id) {
        return response()->json(['message' => "Details for staff member with ID: $id"]);
    })->name('staff.details');
    });