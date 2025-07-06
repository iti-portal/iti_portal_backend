<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Services\NotificationService;
use App\Services\ApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminApplicationService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function getAllApplications(array $filters, int $perPage)
    {
        $query = JobApplication::with([
            'user.profile',
            'job.company.companyProfile',
            'job' => function ($query) {
                $query->select('id', 'company_id', 'title', 'job_type', 'experience_level');
            }
        ]);

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['company_id'])) {
            $query->whereHas('job', function ($q) use ($filters) {
                $q->where('company_id', $filters['company_id']);
            });
        }

        if (isset($filters['job_id'])) {
            $query->where('job_id', $filters['job_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    public function getApplicationById(string $id)
    {
        return JobApplication::with([
            'user.profile',
            'user.skills',
            'job.company.companyProfile',
            'job.job_skills.skill'
        ])->find($id);
    }

    public function updateApplicationStatus(JobApplication $application, string $newStatus, ?string $adminNotes, bool $notifyParties)
    {
        $oldStatus = $application->status;

        DB::beginTransaction();

        try {
            $application->update([
                'status' => $newStatus,
                'company_notes' => $adminNotes ?? $application->company_notes,
            ]);

            if ($notifyParties && $oldStatus !== $newStatus) {
                $this->notificationService->notifyApplicantOfStatusChange($application, $oldStatus);
            }

            DB::commit();

            return $application;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteApplication(string $id)
    {
        $application = JobApplication::findOrFail($id);
        
        DB::beginTransaction();

        try {
            if ($application->cv_path && Storage::disk('public')->exists($application->cv_path)) {
                Storage::disk('public')->delete($application->cv_path);
            }

            if ($application->job) {
                $application->job->decrement('applications_count');
            }

            $application->delete();

            DB::commit();
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getApplicationStatistics()
    {
        return [
            'total_applications' => JobApplication::count(),
            'status_breakdown' => JobApplication::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'applications_this_month' => JobApplication::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'applications_this_week' => JobApplication::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
            'top_companies_by_applications' => JobApplication::select(
                'jobs.company_id',
                'users.email as company_email',
                'cp.company_name',
                'cp.logo as company_profile_image',
                DB::raw('count(*) as applications_count')
            )
                ->join('available_jobs as jobs', 'job_applications.job_id', '=', 'jobs.id')
                ->join('users', 'jobs.company_id', '=', 'users.id')
                ->leftJoin('company_profiles as cp', 'users.id', '=', 'cp.user_id')
                ->groupBy('jobs.company_id', 'users.email', 'cp.company_name', 'cp.logo')
                ->orderByDesc('applications_count')
                ->limit(10)
                ->get(),
        ];
    }
}
