<?php

use App\Http\Controllers\UserProfileController;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;

// display and management user details
Route::middleware(['auth:sanctum', 'email.verified'])->group(function () {
    // Routes for current user
    // This route for getting user profile
    Route::middleware(['can:view profiles'])->get('/profile', [UserProfileController::class, 'getUserProfile'])
        ->name('profile');
    // This route for updating user profile
    Route::middleware(['can:edit own profile'])->put('/profile', [UserProfileController::class, 'updateUserProfile']
    )->name('profile.update');
    // This route for deleting user profile
    Route::middleware(['can:edit own profile'])->delete('/profile', [UserProfileController::class, 'deleteUserProfile']
    )->name('profile.delete');
    
    // This route for getting user profile details by ID
    Route::middleware(['can:view profiles'])->get('/profile/{id}', [UserProfileController::class, 'getUserProfileById'])
        ->name('profile.details');


    // This route for listing all users
    Route::middleware(['can:view profiles'])->get('/itians', [UserProfileController::class, 'getAllItians'])->name('itians.list');

     // route for listing all graduates
     Route::middleware(['can:view alumni profiles'])->get('/iti-graduates', [UserProfileController::class, 'getGraduates'])->name('graduates.list');

     // route for listing all students
     Route::middleware(['can:view student profiles'])->get('/iti-students', [UserProfileController::class, 'getStudents'])->name('students.list');
 
    
    
    // this route for listing all staff members
    Route::get('/staffs', function (Request $request) {
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
    Route::middleware(['auth:sanctum'])->group(function () {
    
    // This route for deleting a user
    Route::middleware(['can:manage users'])->delete('/users/{id}', [UserProfileController::class, 'deleteUserProfileById'])->name('users.delete');
    
    // This route for getting user details by ID we can use the same controller in profile.details route above
    Route::middleware(['can:manage users'])->get('/users/{id}', [UserProfileController::class, 'getUserProfileById'])->name('users.details');
    });
    
    // route for listing all graduates
    Route::middleware(['can:view users'])->get('/graduates', [UserProfileController::class, 'getGraduates'])->name('graduates.list');

    // route for listing all students
    Route::middleware(['can:view users'])->get('/students', [UserProfileController::class, 'getStudents'])->name('students.list');

    // route for listing all users
    Route::middleware(['can:view users'])->get('/users', [UserProfileController::class, 'getAllItians'])->name('users.list');

   
    
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
