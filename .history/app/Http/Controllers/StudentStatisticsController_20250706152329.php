<?php
namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\ArticleLike;
use App\Models\Award;
use App\Models\Certificate;
use App\Models\Connection;
use App\Models\Education;
use App\Models\JobApplication;
use App\Models\Project;
use App\Models\UserProfile;
use App\Models\UserSkill;
use App\Models\WorkExperience;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use 

class StudentStatisticsController extends Controller
{
    private function parseDates(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subYear();
        $endDate   = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        return [$startDate, $endDate];
    }

    private function getPreviousMonthApplicationStats($userId)
    {
        $start = Carbon::now()->subMonth()->startOfMonth();
        $end   = Carbon::now()->subMonth()->endOfMonth();

        return JobApplication::where('user_id', $userId)
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private function getTopAppliedJobs($userId)
    {
        return JobApplication::with('job')
            ->where('user_id', $userId)
            ->select('job_id', DB::raw('count(*) as count'))
            ->groupBy('job_id')
            ->orderByDesc('count')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'title' => $item->job->title ?? 'N/A',
                    'count' => $item->count,
                ];
            });
    }

    public function profileCompletion(Request $request)
    {
        $userId      = auth()->id();
        $userProfile = UserProfile::where('user_id', $userId)->first();

        $completedFields = 0;
        $totalFields     = 0;
        $missingFields   = [];

        if ($userProfile) {
            $profileFields = [
                'first_name', 'last_name', 'username', 'summary', 'phone', 'whatsapp',
                'linkedin', 'github', 'portfolio_url', 'profile_picture', 'cover_photo',
                'branch', 'program', 'track', 'intake', 'student_status',
                'nid_front_image', 'nid_back_image',
            ];

            foreach ($profileFields as $field) {
                $totalFields++;
                if (! empty($userProfile->$field)) {
                    $completedFields++;
                } else {
                    $missingFields[] = str_replace('_', ' ', ucfirst($field));
                }
            }
        }

        $relations = [
            'Education'       => Education::where('user_id', $userId)->exists(),
            'Work Experience' => WorkExperience::where('user_id', $userId)->exists(),
            'Projects'        => Project::where('user_id', $userId)->exists(),
            'Certificates'    => Certificate::where('user_id', $userId)->exists(),
            'Awards'          => Award::where('user_id', $userId)->exists(),
            'Skills'          => UserSkill::where('user_id', $userId)->exists(),
        ];

        foreach ($relations as $label => $exists) {
            $totalFields++;
            if ($exists) {
                $completedFields++;
            } else {
                $missingFields[] = $label;
            }
        }

        $completionPercentage = $totalFields > 0 ? round(($completedFields / $totalFields) * 100, 2) : 0;

        return response()->json([
            'completion_percentage'  => $completionPercentage,
            'missing_fields'         => $missingFields,
            'completed_fields_count' => $completedFields,
            'total_fields_count'     => $totalFields,
        ]);
    }

    public function myApplications(Request $request)
    {
        $userId                = auth()->id();
        [$startDate, $endDate] = $this->parseDates($request);

        $totalApplications = JobApplication::where('user_id', $userId)->count();

        $applicationsByStatus = JobApplication::where('user_id', $userId)
            ->select('status', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('status')
            ->get();

        $applicationsMonthly = JobApplication::where('user_id', $userId)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%M") as month, count(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $previousMonthApplications = $this->getPreviousMonthApplicationStats($userId);
        $topAppliedJobs            = $this->getTopAppliedJobs($userId);

        return response()->json([
            'total_applications'          => $totalApplications,
            'applications_by_status'      => $applicationsByStatus,
            'applications_monthly'        => $applicationsMonthly,
            'previous_month_applications' => $previousMonthApplications,
            'top_applied_jobs'            => $topAppliedJobs,
        ]);
    }

    public function myActivities(Request $request)
    {
        $userId = auth()->id();

        return response()->json([
            'achievements_count'           => Achievement::where('user_id', $userId)->count(),
            'awards_count'                 => Award::where('user_id', $userId)->count(),
            'certificates_count'           => Certificate::where('user_id', $userId)->count(),
            'projects_count'               => Project::where('user_id', $userId)->count(),
            'skills_count'                 => UserSkill::where('user_id', $userId)->count(),
            'work_experiences_count'       => WorkExperience::where('user_id', $userId)->count(),
            'educations_count'             => Education::where('user_id', $userId)->count(),
            'article_likes_count'          => ArticleLike::where('user_id', $userId)->count(),
            'total_connections'            => Connection::where(function ($query) use ($userId) {
                $query->where('requester_id', $userId)->orWhere('addressee_id', $userId);
            })->where('status', 'accepted')->count(),
            'sent_connection_requests'     => Connection::where('requester_id', $userId)->where('status', 'pending')->count(),
            'received_connection_requests' => Connection::where('addressee_id', $userId)->where('status', 'pending')->count(),
        ]);
    }

    public function studentStats(Request $request)
    {
        $profileCompletion = $this->profileCompletion($request)->getData(true);
        $myApplications    = $this->myApplications($request)->getData(true);
        $myActivities      = $this->myActivities($request)->getData(true);

        return response()->json([
            'message' => 'Student statistics loaded successfully.',
            'data'    => array_merge($profileCompletion, $myApplications, $myActivities),
        ]);
    }
    public function getStudentAndCompanyStats(Request $request)
    {
        $studentStats = $this->studentStats($request)->getData(true);
        $companyStats = $this->companyStats($request)->getData(true);

        return response()->json([
            'message' => 'Student and company statistics loaded successfully.',
            'data'    => array_merge($studentStats, $companyStats),
        ]);
    }}