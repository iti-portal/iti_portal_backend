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
        $studentController = new StudentStatisticsController();
        $companyController = new CompanyStatisticsController();
        $adminController   = new AdminStatisticsController();

        $studentStats  = $studentController->studentStats($request)->getData(true);
        $companyStats  = $companyController->companyStats($request)->getData(true);
        $adminStats    = $adminController->adminStats($request)->getData(true);

        return response()->json([
            'message' => 'General statistics loaded successfully.',
            'data'    => array_merge($studentStats, $companyStats, $adminStats),
        ]);
    }
}
