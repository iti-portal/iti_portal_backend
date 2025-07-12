<?php
namespace App\Http\Controllers;

use App\Models\AvailableJob;
use App\Models\JobApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyStatisticsController extends Controller
{

    private function parseDates(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subYear();
        $endDate   = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        return [$startDate, $endDate];
    }

    private function getPreviousMonthRange()
    {
        return [
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()->endOfMonth(),
        ];
    }

    private function monthlyComparisons($companyId)
    {
        [$prevStart, $prevEnd] = $this->getPreviousMonthRange();

        $prevMonthJobs = AvailableJob::where('company_id', $companyId)
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $prevMonthApplications = JobApplication::whereHas('job', fn($q) => $q->where('company_id', $companyId))
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        $prevMonthHired = JobApplication::whereHas('job', fn($q) => $q->where('company_id', $companyId))
            ->where('status', 'hired')
            ->whereBetween('created_at', [$prevStart, $prevEnd])
            ->count();

        return [
            'previous_jobs'         => $prevMonthJobs,
            'previous_applications' => $prevMonthApplications,
            'previous_hired'        => $prevMonthHired,
        ];
    }

    public function jobPerformance(Request $request)
    {
        $companyId             = auth()->id();
        [$startDate, $endDate] = $this->parseDates($request);

        $companyJobsQuery = AvailableJob::where('company_id', $companyId);

        $totalJobsPosted = $companyJobsQuery->count();
        $activeJobs      = (clone $companyJobsQuery)->where('status', 'active')->count();
        $featuredJobs    = (clone $companyJobsQuery)->where('is_featured', true)->count();

        $jobsPostedMonthly = (clone $companyJobsQuery)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%M") as month, count(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $applicationsReceivedMonthly = JobApplication::whereHas('job', fn($q) => $q->where('company_id', $companyId))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%M") as month, count(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $totalApplications     = $applicationsReceivedMonthly->sum('count');
        $avgApplicationsPerJob = $totalJobsPosted > 0 ? round($totalApplications / $totalJobsPosted, 2) : 0;

        $hiredCount = JobApplication::whereHas('job', fn($q) => $q->where('company_id', $companyId))
            ->where('status', 'hired')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $hiringQualityRate = $totalApplications > 0
        ? round(($hiredCount / $totalApplications) * 100, 2)
        : 0;

        $previousComparisons = $this->monthlyComparisons($companyId);

        return response()->json([
            'total_jobs_posted'             => $totalJobsPosted,
            'active_jobs'                   => $activeJobs,
            'featured_jobs'                 => $featuredJobs,
            'jobs_posted_monthly'           => $jobsPostedMonthly,
            'applications_received_monthly' => $applicationsReceivedMonthly,
            'avg_applications_per_job'      => $avgApplicationsPerJob,
            'hired_applications'            => $hiredCount,
            'hiring_quality_rate'           => $hiringQualityRate,
            'monthly_comparisons'           => array_merge([
                'current_jobs'         => $totalJobsPosted,
                'current_applications' => $totalApplications,
                'current_hired'        => $hiredCount,
            ], $previousComparisons),
        ]);
    }

    public function applicantStatus(Request $request)
    {
        $companyId             = auth()->id();
        [$startDate, $endDate] = $this->parseDates($request);

        $baseQuery = fn($q) => $q->where('company_id', $companyId);

      $applicationsByStatusCount = JobApplication::whereHas('job', $baseQuery)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $applications = JobApplication::with(['user.profile'])
            ->whereHas('job', $baseQuery)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $applicationsByStatus = $applications->groupBy(function ($application) {
            return $application->status ?? 'no_status';
        })->map(function ($apps) {
            return $apps->map(function ($application) {
                return [
                    'id'              => $application->id,
                    'job_id'          => $application->job_id,
                    'status'          => $application->status ?? 'no_status',
                    'applied_at'      => $application->created_at,
                    'applicant_name'  => optional($application->user)->getFullNameAttribute(),
                    'applicant_email' => optional($application->user)->email,
                    'profile_picture' => optional($application->user->profile)->profile_picture,

                ];
            })->values();
        });

        $cvsDownloaded = JobApplication::whereHas('job', $baseQuery)
            ->whereNotNull('cv_downloaded_at')
            ->whereBetween('cv_downloaded_at', [$startDate, $endDate])
            ->count();

        $profilesViewed = JobApplication::whereHas('job', $baseQuery)
            ->whereNotNull('profile_viewed_at')
            ->whereBetween('profile_viewed_at', [$startDate, $endDate])
            ->count();

        $reviewedApplications = JobApplication::whereHas('job', $baseQuery)
            ->where('is_reviewed', true)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return response()->json([
            'applications_by_status_count' => $applicationsByStatusCount,
            'applications_by_status' => $applicationsByStatus,
            'cvs_downloaded'         => $cvsDownloaded,
            'profiles_viewed'        => $profilesViewed,
            'reviewed_applications'  => $reviewedApplications,
        ]);
    }

    public function getStatistics(Request $request)
    {
        $jobPerformance  = $this->jobPerformance($request)->getData(true);
        $applicantStatus = $this->applicantStatus($request)->getData(true);

        return response()->json([
            'message' => 'Company statistics retrieved successfully.',
            'data'    => array_merge($jobPerformance, $applicantStatus),
        ]);
    }
}
