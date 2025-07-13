<?php

use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

// All Admin routes require authentication and approval
Route::middleware(['auth:sanctum', 'account.approved'])->group(function () {
    
    // User Management routes (restricted to admins)
    Route::middleware('role:admin|staff')->prefix('admin')->group(function () {
        Route::get('pending-users', [UserManagementController::class, 'pendingUsers']);
        Route::get('retrieve-staff', [UserManagementController::class, 'getStaff']);
        Route::post('approve-user/{user}', [UserManagementController::class, 'approveUser']);
        Route::post('reject-user/{user}', [UserManagementController::class, 'rejectUser']);
        Route::post('create-staff', [UserManagementController::class, 'createStaff']);
        Route::post('suspend-user/{user}', [UserManagementController::class, 'suspendUser']);
        Route::post('unsuspend-user/{user}', [UserManagementController::class, 'unsuspendUser']);
        Route::post('mark-student-as-graduate/{user}', [UserManagementController::class, 'markStudentAsGraduate'])
        ->middleware('role:admin');
    });
    
    // This route is also an admin-level user management task
    Route::delete('admin/delete-staff/{user}', [UserManagementController::class, 'deleteStaff']);
});