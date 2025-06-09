<?php
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ExternalAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TestController;

require __DIR__ . '/api/users.route.php';
require __DIR__ . '/api/companies.route.php';
require __DIR__ . '/api/eductionAndSkills.route.php';
require __DIR__ . '/api/oldAchievments.route.php';
require __DIR__ . '/api/jobs.route.php';
require __DIR__ . '/api/achievments.route.php';
require __DIR__ . '/api/articles.route.php';
require __DIR__ . '/api/services.route.php';
require __DIR__ . '/api/connections.route.php';

Route::get('test/seccess', [TestController::class, 'index'])
    ->name('test.success');
Route::get('test/error', [TestController::class, 'error']);


// Public API routes
Route::post('auth/register', [RegistrationController::class, 'initialRegister']);
Route::post('auth/login', [AuthController::class, 'login']);


// Sanctum only routes (authenticated but allow unverified)
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('user-status', [RegistrationController::class, 'showRegistrationStep']);
    Route::post('resend-verification', [AuthController::class, 'resendVerificationEmail']);
    Route::post('logout', [AuthController::class, 'logout']);
});

// Registration completion routes (require auth + specific steps)
Route::middleware(['auth:sanctum', 'email.verified'])->group(function () {
    Route::post('registration/complete-profile', [RegistrationController::class, 'completeProfile'])
        ->middleware('role:student|alumni', 'allow.step:user_profile');
    
    Route::post('registration/complete-company-profile', [RegistrationController::class, 'completeCompanyProfile'])
        ->middleware('role:company', 'allow.step:company_profile');
    
    Route::post('registration/upload-nid', [RegistrationController::class, 'uploadNid'])
        ->middleware('role:student|alumni', 'allow.step:nid_upload');
});

// Protected API routes
Route::middleware('auth:sanctum', 'account.approved')->group(function () {
    Route::get('auth/user', [AuthController::class, 'user']);
    
    // User Management routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('pending-users', [UserManagementController::class, 'pendingUsers']);
        Route::post('approve-user/{user}', [UserManagementController::class, 'approveUser']);
        Route::post('reject-user/{user}', [UserManagementController::class, 'rejectUser']);
        Route::post('create-staff', [UserManagementController::class, 'createStaff']);
    });
});


// External authentication API
Route::prefix('external-auth')->group(function () {
    Route::post('verify-token', [ExternalAuthController::class, 'verifyToken']);
    Route::post('refresh-token', [ExternalAuthController::class, 'refreshToken'])
        ->middleware('jwt.auth');
});

// External API routes (JWT protected)
Route::middleware('jwt.auth')->prefix('external-auth')->group(function () {
    Route::get('user-profile', function () {
        return response()->json([
            'user' => auth()->user()->load('profile', 'companyProfile', 'roles'),
        ]);
    });
});

