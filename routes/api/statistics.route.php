<?php

use App\Http\Controllers\Admin\AdminStatisticsController;
use App\Http\Controllers\CompanyStatisticsController;
use App\Http\Controllers\StatisticController;
use App\Http\Controllers\StudentStatisticsController;
use Illuminate\Support\Facades\Route;


Route::get("/admin/statistics", [AdminStatisticsController::class, 'getStatistics'])
    ->middleware(['auth:sanctum', 'role:admin|staff'])
    ->name('admin.statistics');
Route::get("/company/statistics", [CompanyStatisticsController::class, 'getStatistics'])
    ->middleware(['auth:sanctum', 'role:company'])
    ->name('company.statistics');
Route::get("/student/statistics", [StudentStatisticsController::class, 'getStatistics'])
    ->middleware(['auth:sanctum', 'role:student|alumni'])
    ->name('student.statistics');

Route::get('/general-statistics', [StatisticController::class, 'generalStats'])
    ->middleware(['auth:sanctum', 'role:student|alumni|company|admin|staff'])
    ->name('general.statistics');
Route::get('/home-statistics', [StatisticController::class, 'homeStats'])->name('home.statistics');



