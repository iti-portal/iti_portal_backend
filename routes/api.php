<?php
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ExternalAuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Controller;

require __DIR__ . '/api/users.route.php';
require __DIR__ . '/api/companies.route.php';
require __DIR__ . '/api/education.route.php';
require __DIR__ . '/api/projects.route.php';
require __DIR__ . '/api/oldAchievments.route.php';
require __DIR__ . '/api/jobs.route.php';
require __DIR__ . '/api/achievments.route.php';
require __DIR__ . '/api/articles.route.php';
require __DIR__ . '/api/services.route.php';
require __DIR__ . '/api/connections.route.php';
require __DIR__ . '/api/skills.route.php';
require __DIR__ . '/api/workExperience.route.php';
require __DIR__. '/api/userprofiles.route.php';
require __DIR__. '/api/jobApplication.route.php';
require __DIR__. '/api/awards.route.php';
require __DIR__. '/api/certificates.route.php';
require __DIR__ . '/api/chat.route.php';
require __DIR__. '/api/statistics.route.php';

use App\Http\Controllers\Auth\PasswordResetController;

// Public API routes
Route::post('auth/register', [RegistrationController::class, 'registerIndividual']);
Route::post('auth/register-company', [RegistrationController::class, 'registerCompany']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/external-login', [AuthController::class, 'externalLogin'])
    ->middleware(['guest', 'throttle:5,1'])
    ->name('external.login');

// Email verification routes
Route::get('auth/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');



// Reset password routes
Route::prefix('auth')->middleware('guest')->group(function () {
    Route::post('forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('password.request');
    Route::post('reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
});

// Sanctum only routes (authenticated but allow unverified)
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('user-status', [RegistrationController::class, 'showRegistrationStep']);
    Route::post('resend-verification', [AuthController::class, 'resendVerificationEmail'])
    ->middleware('throttle:6,1')
    ->name('verification.send');
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
        Route::get('retrieve-staff', [UserManagementController::class, 'getStaff']);
        Route::post('approve-user/{user}', [UserManagementController::class, 'approveUser']);
        Route::post('reject-user/{user}', [UserManagementController::class, 'rejectUser']);
        Route::post('create-staff', [UserManagementController::class, 'createStaff']);
        Route::post('suspend-user/{user}', [UserManagementController::class, 'suspendUser']);
        Route::post('unsuspend-user/{user}', [UserManagementController::class, 'unsuspendUser']);
        Route::post('delete-staff/{user}', [UserManagementController::class, 'deleteStaff']);
    }); 
        Route::post('mark-student-as-graduate/{user}', [UserManagementController::class, 'markStudentAsGraduate']);
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
