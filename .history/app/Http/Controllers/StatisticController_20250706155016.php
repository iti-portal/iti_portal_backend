<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\CompanyStatisticsController;
use App\Http\Controllers\StudentStatisticsController;
use App\Http\Controllers\Admin\AdminStatisticsController;

class StatisticController extends Controller
{
    public function generalStats(Request $request)
    {
    $user= auth()->user();
    if ($user->hasRole('admin') || $user->hasRole('staff')) {
        $adminController = new AdminStatisticsController();
    }
    else if ($user->hasRole('company')) {
        $companyController = new CompanyStatisticsController();
    } else if ($user->hasRole('student') || $user->hasRole('alumni')) {
        $studentController = new StudentStatisticsController();
    } else {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    return response()->json([
        'message' => 'General statistics loaded successfully.',
        'data'    => [
            'admin_stats'    => isset($adminController) ? $adminController->adminStats($request)->getData(true) : null,
            'company_stats'  => isset($companyController) ? $companyController->companyStats($request)->getData(true) : null,
            'student_stats'  => isset($studentController) ? $studentController->studentStats($request)->getData(true) : null,
        ],
    ]); }
}
