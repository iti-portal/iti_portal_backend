<?php

namespace App\Services;

use App\Models\AvailableJob;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ApplicationService
{
    protected NotificationService $notificationService;
    protected SkillMatchingService $skillMatchingService;

    public function __construct(
        NotificationService $notificationService,
        SkillMatchingService $skillMatchingService
    ) {
        $this->notificationService = $notificationService;
        $this->skillMatchingService = $skillMatchingService;
    }

    /**
     * Get user's job applications with related data
     */
    public function getUserApplications(int $userId, array $filters = []): array
    {
        $query = JobApplication::with('job.company.companyProfile')
            ->where('user_id', $userId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['company_id'])) {
            $query->whereHas('job', function ($q) use ($filters) {
                $q->where('company_id', $filters['company_id']);
            });
        }

        $applications = $query->latest()->get();

        return [
            'applications' => $applications,
            'total_applications' => $applications->count()
        ];
    }

    /**
     * Get a specific application for a user
     */
    public function getUserApplication(string $applicationId, int $userId): ?JobApplication
    {
        return JobApplication::with(['job.company.companyProfile'])
            ->where('id', $applicationId)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Check if user has already applied for a job
     */
    public function hasUserAppliedForJob(int $userId, int $jobId): bool
    {
        return JobApplication::where('user_id', $userId)
            ->where('job_id', $jobId)
            ->exists();
    }

    /**
     * Check if job is still accepting applications
     */
    public function isJobAcceptingApplications(int $jobId): array
    {
        $job = AvailableJob::find($jobId);
        
        if (!$job) {
            return ['valid' => false, 'message' => 'Job not found'];
        }

        if ($job->status !== 'active') {
            return ['valid' => false, 'message' => 'This job is no longer accepting applications.'];
        }

        if ($job->application_deadline < now()) {
            return ['valid' => false, 'message' => 'This job is no longer accepting applications.'];
        }

        return ['valid' => true, 'job' => $job];
    }

    /**
     * Create a new job application
     */
    public function createApplication(array $data, UploadedFile $cvFile): JobApplication
    {
        return DB::transaction(function () use ($data, $cvFile) {
            // Handle CV upload
            $cvPath = $cvFile->store('cv-documents', 'public');

            // Create application
            $application = JobApplication::create([
                'user_id' => $data['user_id'],
                'job_id' => $data['job_id'],
                'cover_letter' => $data['cover_letter'],
                'cv_path' => $cvPath,
                'status' => 'applied',
                'applied_at' => now(),
            ]);

            // Increment applications count
            $job = AvailableJob::find($data['job_id']);
            $job->increment('applications_count');

            // Send notification to company
            $this->notificationService->notifyCompanyOfNewApplication($application);

            return $application;
        });
    }

    /**
     * Get application that can be withdrawn by user
     */
    public function getWithdrawableApplication(string $applicationId, int $userId): ?JobApplication
    {
        return JobApplication::where('id', $applicationId)
            ->where('user_id', $userId)
            ->where('status', 'applied')
            ->first();
    }

    /**
     * Withdraw a job application
     */
    public function withdrawApplication(JobApplication $application): void
    {
        DB::transaction(function () use ($application) {
            // Delete CV file
            if ($application->cv_path) {
                Storage::disk('public')->delete($application->cv_path);
            }

            // Send notification to company about withdrawal
            $this->notificationService->notifyCompanyOfWithdrawal($application);

            // Decrement applications count
            $application->job->decrement('applications_count');

            // Delete application
            $application->delete();
        });
    }

    /**
     * Get applications for a company with filters
     */
    public function getCompanyApplications(int $companyId, array $filters = []): Collection
    {
        $query = JobApplication::with(['user.profile', 'user.skills', 'job'])
            ->whereHas('job', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });

        // Filter by job if provided
        if (isset($filters['job_id'])) {
            $query->where('job_id', $filters['job_id']);
        }

        // Filter by status if provided
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $applications = $query->latest()->get();

        // Add match scores if requested
        if (isset($filters['include_match_score']) && $filters['include_match_score']) {
            $applications = $applications->map(function ($application) {
                $matchData = $this->skillMatchingService->calculateMatchScore($application->user, $application->job);
                $application->match_data = $matchData;
                return $application;
            })->sortByDesc('match_data.match_score')->values();
        }

        return $applications;
    }

    /**
     * Get application that belongs to company
     */
    public function getCompanyApplication(string $applicationId, int $companyId): ?JobApplication
    {
        return JobApplication::with(['job', 'user'])
            ->whereHas('job', function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->find($applicationId);
    }

    /**
     * Update application status
     */
    public function updateApplicationStatus(JobApplication $application, string $newStatus, ?string $companyNotes = null): JobApplication
    {
        return DB::transaction(function () use ($application, $newStatus, $companyNotes) {
            $oldStatus = $application->status;

            $application->update([
                'status' => $newStatus,
                'company_notes' => $companyNotes,
            ]);

            // Send notification to applicant about status change
            if ($oldStatus !== $newStatus) {
                $this->notificationService->notifyApplicantOfStatusChange($application, $oldStatus);
            }

            return $application->fresh();
        });
    }

    /**
     * Get application for CV download with role-based access
     */
    public function getApplicationForCVDownload(string $applicationId, User $user): array
    {
        $application = null;
        $isCompanyDownload = false;

        // For students/alumni - their own applications
        if ($user->hasRole(['student', 'alumni'])) {
            $application = JobApplication::where('id', $applicationId)
                ->where('user_id', $user->id)
                ->first();
        }
        // For companies - applications to their jobs
        elseif ($user->hasRole('company')) {
            $isCompanyDownload = true;
            $application = JobApplication::whereHas('job', function($query) use ($user) {
                $query->where('company_id', $user->id);
            })->find($applicationId);
        }

        if (!$application) {
            return ['success' => false, 'message' => 'Application not found or not accessible'];
        }

        if (!$application->cv_path || !Storage::disk('public')->exists($application->cv_path)) {
            return ['success' => false, 'message' => 'CV file not found'];
        }

        return [
            'success' => true,
            'application' => $application,
            'is_company_download' => $isCompanyDownload
        ];
    }

    /**
     * Track CV download by company and update status
     */
    public function trackCVDownload(JobApplication $application): void
    {
        DB::transaction(function () use ($application) {
            // Update tracking fields
            $updateData = [
                'cv_downloaded_at' => now(),
                'is_reviewed' => true,
            ];

            // Auto-update status to 'reviewed' if still 'applied'
            if ($application->status === 'applied') {
                $updateData['status'] = 'reviewed';

                // Send notification to applicant about status change
                $this->notificationService->notifyApplicantOfStatusChange($application, 'applied');
            }

            $application->update($updateData);
        });
    }

    /**
     * Track profile view by company
     */
    public function trackProfileView(JobApplication $application): array
    {
        return DB::transaction(function () use ($application) {
            // Update tracking fields
            $updateData = [
                'profile_viewed_at' => now(),
                'is_reviewed' => true,
            ];

            // Auto-update status to 'reviewed' if still 'applied'
            if ($application->status === 'applied') {
                $updateData['status'] = 'reviewed';

                // Send notification to applicant about status change
                $this->notificationService->notifyApplicantOfStatusChange($application, 'applied');
            }

            $application->update($updateData);

            return [
                'application_id' => $application->id,
                'profile_viewed_at' => $application->profile_viewed_at,
                'status' => $application->status,
                'is_reviewed' => $application->is_reviewed,
            ];
        });
    }

    /**
     * Batch update application statuses
     */
    public function batchUpdateStatus(array $applicationIds, string $newStatus, int $companyId, ?string $companyNotes = null): array
    {
        Log::info('Batch update status called', [
            'applicationIds' => $applicationIds,
            'newStatus' => $newStatus,
            'companyId' => $companyId,
            'companyNotes' => $companyNotes
        ]);

        // Eager load with all needed relations for validation, notifications, and response.
        $applicationsToUpdate = JobApplication::with(['job.company.companyProfile', 'user.profile'])
            ->whereIn('id', $applicationIds)
            ->whereHas('job', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->get();

        Log::info('Applications found for batch update', [
            'count' => $applicationsToUpdate->count(),
            'application_ids_found' => $applicationsToUpdate->pluck('id')->toArray()
        ]);

        if ($applicationsToUpdate->isEmpty()) {
            return ['success' => false, 'message' => 'No valid applications found for the given IDs.'];
        }

        $uniqueRequestedIds = array_unique($applicationIds);
        if ($applicationsToUpdate->count() !== count($uniqueRequestedIds)) {
            $foundIds = $applicationsToUpdate->pluck('id')->toArray();
            $missingIds = array_diff($uniqueRequestedIds, $foundIds);
            $message = 'Some applications were not found or are not accessible to your company. Missing or invalid IDs: ' . implode(', ', $missingIds);
            return ['success' => false, 'message' => $message];
        }
        
        $updatableApplicationIds = $applicationsToUpdate->pluck('id')->toArray();

        $updateData = ['status' => $newStatus];
        if ($companyNotes !== null) {
            $updateData['company_notes'] = $companyNotes;
        }

        try {
            $updatedCount = DB::transaction(function () use ($updatableApplicationIds, $updateData) {
                return JobApplication::whereIn('id', $updatableApplicationIds)->update($updateData);
            });
            Log::info('Batch update query result', ['updated_count' => $updatedCount]);
        } catch (\Exception $e) {
            Log::error('Batch update failed: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during the batch update.'];
        }

        // Reload applications to get their updated status and notes from the database
        $updatedApplications = JobApplication::with(['job.company.companyProfile', 'user.profile'])
            ->whereIn('id', $updatableApplicationIds)
            ->get();

        // Handle notifications efficiently without N+1 queries
        foreach ($updatedApplications as $application) {
            $oldStatus = $application->getOriginal('status'); // Get original status before update

            if ($oldStatus !== $newStatus) {
                try {
                    $this->notificationService->notifyApplicantOfStatusChange($application, $oldStatus);
                } catch (\Exception $notificationError) {
                    Log::warning("Failed to send notification for application {$application->id}: " . $notificationError->getMessage());
                }
            }
        }

        return [
            'success' => true,
            'updated_count' => $updatedCount,
            'total_requested' => count($uniqueRequestedIds),
            'applications' => $updatedApplications, // Return the reloaded collection
            'message' => "Successfully updated {$updatedCount} application(s) to '{$newStatus}' status."
        ];
    }

    /**
     * Get job applications with match scores
     */
    public function getMatchedApplications(AvailableJob $job): Collection
    {
        return $this->skillMatchingService->getApplicationsWithMatchScores($job);
    }

    /**
     * Get job application statistics
     */
    public function getJobApplicationStats(AvailableJob $job): array
    {
        return [
            'total' => $job->applications()->count(),
            'status_breakdown' => $job->applications()
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'applications_this_week' => $job->applications()
                ->whereBetween('created_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])
                ->count(),
            'applications_this_month' => $job->applications()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Get user's skill match for a job
     */
    public function getUserJobMatch(User $user, int $jobId): array
    {
        $job = AvailableJob::with(['job_skills.skill'])->find($jobId);

        if (!$job) {
            return ['success' => false, 'message' => 'Job not found'];
        }

        if ($job->status !== 'active' || $job->application_deadline < now()) {
            return ['success' => false, 'message' => 'This job is no longer accepting applications.'];
        }

        $matchData = $this->skillMatchingService->getUserJobMatch($user, $job);

        return ['success' => true, 'match_data' => $matchData];
    }

    /**
     * Verify job belongs to company
     */
    public function getCompanyJob(int $jobId, int $companyId): ?AvailableJob
    {
        return AvailableJob::where('id', $jobId)
            ->where('company_id', $companyId)
            ->first();
    }
}
