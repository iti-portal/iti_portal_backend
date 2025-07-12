<?php

use App\Http\Controllers\CompanyProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Company Profiles routes
Route::prefix('companies')->middleware('auth:sanctum')->group(function () {
    // Company-specific profile actions
    Route::middleware('role:company')->group(function () {
        Route::get('/my-profile', [CompanyProfileController::class, 'myCompanyProfile']); // GET /api/companies/my-profile
        Route::put('/edit-profile', [CompanyProfileController::class, 'editCompanyProfile']); // PUT /api/companies/my-profile
        Route::post('/my-profile/logo', [CompanyProfileController::class, 'changeCompanyImage']); // POST /api/companies/my-profile/logo
    });

    // Admin/Staff actions
    Route::middleware('role:admin|staff')->group(function () {
        Route::post('/{user}/suspend', [CompanyProfileController::class, 'suspendCompany']); // POST /api/companies/{user}/suspend (user is the ID of the user associated with the company)
    });

    // Publicly accessible companies (approved or suspended)
    Route::get('/', [CompanyProfileController::class, 'getAllCompaniesProfiles']); // GET /api/companies
    Route::get('/{companyProfile}', [CompanyProfileController::class, 'getCompanyProfile']); // GET /api/companies/{companyProfile}

    // Admin/Staff/Company actions (for delete, since it applies to a specific companyProfile ID)
    Route::middleware('role:admin|staff|company')->group(function () {
        Route::delete('/{companyProfile}', [CompanyProfileController::class, 'deleteCompany']); // DELETE /api/companies/{companyProfile}
    });
});
