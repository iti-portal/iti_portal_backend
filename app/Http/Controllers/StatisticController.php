<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Admin\AdminStatisticsController;
use App\Http\Controllers\CompanyStatisticsController;
use App\Http\Controllers\StudentStatisticsController;
use App\Models\JobApplication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    private function parseDates(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subYear();
        $endDate   = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        return [$startDate, $endDate];
    }

    public function generalStats(Request $request)
    {

        $user = auth()->user();

        if ($user->hasAnyRole(['admin', 'staff'])) {
            $controller = new AdminStatisticsController();
        } elseif ($user->hasRole('company')) {
            $controller = new CompanyStatisticsController();
        } elseif ($user->hasAnyRole(['student', 'alumni'])) {
            $controller = new StudentStatisticsController();
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $controller->getStatistics($request);
    }

    public function homeStats(Request $request)
    {

        [$startDate, $endDate] = $this->parseDates($request);

        $studentsCount = User::whereNotNull('email_verified_at')
            ->whereHas('roles', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'student');
                });
            })->count();

        $alumniCount = User::whereNotNull('email_verified_at')
            ->whereHas('roles', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'alumni');
                });
            })->count();

        $companyCount = User::whereNotNull('email_verified_at')
            ->whereHas('roles', function ($query) {
                $query->where('name', 'company');
            })->count();


        $getHiredUsersCount = JobApplication::where('status', 'hired')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->distinct('user_id')
            ->count('user_id');

        $denominator = $studentsCount + $alumniCount;
        $hiredRate   = $denominator > 0
        ? round(($getHiredUsersCount / $denominator) * 100, 2)
        : 0;

        return response()->json([
            'students_count'    => $studentsCount,
            'alumni_count'      => $alumniCount,
            'company_count'     => $companyCount,
            'hired_users_count' => $getHiredUsersCount,
            'total_users_count' => User::whereNotNull('email_verified_at')->count(),
            'hired_rate'        => $hiredRate,
            'start_date'        => $startDate->toDateString(),
            'end_date'          => $endDate->toDateString(),
        ]);
    }
}
