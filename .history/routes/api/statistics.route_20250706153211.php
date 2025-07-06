<?php

use App\Http\Controllers\Admin\AdminStatisticsController;
use App\Http\Controllers\CompanyStatisticsController;
use App\Http\Controllers\StudentStatisticsController;
use Illuminate\Support\Facades\Route;


Route::get("/admin/statistics", [AdminStatisticsController::class, 'adminStats'])
    ->middleware(['auth:sanctum', 'role:admin|staff'])
    ->name('admin.statistics');
Route::get("/company/statistics", [CompanyStatisticsController::class, 'companyStats'])
    ->middleware(['auth:sanctum', 'role:company'])
    ->name('company.statistics');
Route::get("/student/statistics", [StudentStatisticsController::class, 'studentStats'])
    ->middleware(['auth:sanctum', 'role:student|alumni'])
    ->name('student.statistics');

Route::get('/student-company-stats', [StudentStatisticsController::class, 'getStudentAndCompanyStats'])
    ->middleware(['auth:sanctum', 'role:student|alumni|company'])
    ->name('student.company.statistics');


