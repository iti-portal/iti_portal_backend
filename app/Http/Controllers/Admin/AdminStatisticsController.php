<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Article;
use App\Models\AvailableJob;
use App\Models\Award;
use App\Models\Certificate;
use App\Models\Connection;
use App\Models\JobApplication;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatisticsController extends Controller
{
    private function parseDates(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subYear();
        $endDate   = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        return [$startDate, $endDate];
    }

    private function getMonthlyStats($model, $startDate, $endDate)
    {
        return $model::selectRaw('DATE_FORMAT(created_at, "%Y-%M") as month, count(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    public function userStats(Request $request)
    {
        [$startDate, $endDate] = $this->parseDates($request);

        return response()->json([
            'total_users'       => User::count(),
            'new_users_monthly' => $this->getMonthlyStats(User::class, $startDate, $endDate),
            'users_by_status'   => User::select('status', DB::raw('count(*) as count'))->groupBy('status')->get(),
            'users_by_role'     => DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.name as role', DB::raw('count(*) as count'))
                ->groupBy('roles.name')
                ->get(),
            'verified_users'    => User::whereNotNull('email_verified_at')->count(),
            'unverified_users'  => User::whereNull('email_verified_at')->count(),
        ]);
    }

    public function jobStats(Request $request)
    {
        [$startDate, $endDate] = $this->parseDates($request);

        return response()->json([
            'total_jobs'               => AvailableJob::count(),
            'new_jobs_monthly'         => $this->getMonthlyStats(AvailableJob::class, $startDate, $endDate),
            'total_applications'       => JobApplication::count(),
            'new_applications_monthly' => $this->getMonthlyStats(JobApplication::class, $startDate, $endDate),
            'applications_by_status'   => JobApplication::select('status', DB::raw('count(*) as count'))->groupBy('status')->get(),
            'jobs_by_status'           => AvailableJob::select('status', DB::raw('count(*) as count'))->groupBy('status')->get(),
        ]);
    }

    public function contentStats(Request $request)
    {
        [$startDate, $endDate] = $this->parseDates($request);

        return response()->json([
            'total_articles'           => Article::count(),
            'new_articles_monthly'     => $this->getMonthlyStats(Article::class, $startDate, $endDate),
            'total_projects'           => Project::count(),
            'new_projects_monthly'     => $this->getMonthlyStats(Project::class, $startDate, $endDate),
            'total_achievements'       => Achievement::count(),
            'new_achievements_monthly' => $this->getMonthlyStats(Achievement::class, $startDate, $endDate),
            'total_awards'             => Award::count(),
            'new_awards_monthly'       => $this->getMonthlyStats(Award::class, $startDate, $endDate),
            'total_certificates'       => Certificate::count(),
            'new_certificates_monthly' => $this->getMonthlyStats(Certificate::class, $startDate, $endDate),
        ]);
    }

    public function connectionStats(Request $request)
    {
        [$startDate, $endDate] = $this->parseDates($request);

        return response()->json([
            'total_connections'       => Connection::count(),
            'new_connections_monthly' => $this->getMonthlyStats(Connection::class, $startDate, $endDate),
            'connections_by_status'   => Connection::select('status', DB::raw('count(*) as count'))->groupBy('status')->get(),
        ]);
    }

    private function getTopJobsByApplications()
    {
        return DB::table('job_applications')
            ->join('available_jobs', 'job_applications.job_id', '=', 'available_jobs.id')
            ->select('available_jobs.title as job_title', DB::raw('COUNT(job_applications.id) as applications'))
            ->groupBy('job_applications.job_id', 'available_jobs.title')
            ->orderByDesc('applications')
            ->limit(5)
            ->get();
    }

    private function getTopPostedJobTitles()
    {
        return AvailableJob::select('title', DB::raw('COUNT(*) as count'))
            ->groupBy('title')
            ->orderByDesc('count')
            ->limit(5)
            ->get();
    }

    public function getStatistics(Request $request)
    {
        [$startDate, $endDate] = $this->parseDates($request);

        $userStats       = $this->userStats($request)->getData(true);
        $jobStats        = $this->jobStats($request)->getData(true);
        $contentStats    = $this->contentStats($request)->getData(true);
        $connectionStats = $this->connectionStats($request)->getData(true);

        $monthlyHiredApplications = JobApplication::selectRaw('DATE_FORMAT(created_at, "%Y-%M") as month, count(*) as count')
            ->where('status', 'hired')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'message' => 'Admin statistics retrieved successfully.',
            'data'    => [
                'users'       => $userStats,
                'jobs'        => $jobStats,
                'content'     => $contentStats,
                'connections' => $connectionStats,
                'extra'       => [
                    'monthly_hired_applications' => $monthlyHiredApplications,
                    'top_jobs_by_applications'   => $this->getTopJobsByApplications(),
                    'top_posted_job_titles'      => $this->getTopPostedJobTitles(),
                ],
            ],
        ]);
    }
}
