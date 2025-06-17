<?php

use App\Http\Controllers\UserProfileController;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;

// display and management user details
Route::middleware(['auth:sanctum'])->group(function () {
    // Routes for current user
    // This route for getting user profile
    Route::get('/profile', [UserProfileController::class, 'getUserProfile'])
        ->name('profile');
    // This route for updating user profile
    Route::post('/profile', [UserProfileController::class, 'updateUserProfile']
    )->name('profile.update');
    // This route for deleting user profile
    Route::delete('/profile', [UserProfileController::class, 'deleteUserProfile'])
        ->middleware('can:delete-profile') 
        ->withoutMiddleware(['auth:sanctum']
    )->name('profile.delete');
    
    // This route for getting user profile details by ID
    Route::get('/profile/{id}', [UserProfileController::class, 'getUserProfileById'])
        ->name('profile.details');


    // This route for listing all users
    Route::get('/itians', [UserProfileController::class, 'listItians'])->name('itians.list');
    // this route for listing all staff members
    Route::get('/staff', function (Request $request) {
        return response()->json(['message' => 'List of staff members']);
    })->name('staff.list');

    // route for update profile picture
    Route::post('/profile-picture', [UserProfileController::class, 'updateUserProfileImage'])
        ->name('profile.picture.update');

    // route for update cover photo
    Route::post('/cover-photo', [UserProfileController::class, 'updateUserCoverPhoto'])
        ->name('profile.cover.update');
    });
    
    
    // routes for user's management by admin and staff
    Route::middleware([])->group(function () {
    
    // This route for deleting a user
    Route::delete('/users/{id}', [UserProfileController::class, 'deleteUserProfileById'])->name('users.delete');
    // This route for getting user details by ID we can use the same controller in profile.details route above
    Route::get('/users/{id}', [UserProfileController::class, 'getUserProfileById'])->name('users.details');
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
    Route::get('/verify-new-email/{user}', [UserProfileController::class, 'verifyNewEmail'])
    ->name('verify-new-email')
    ->middleware('signed');
