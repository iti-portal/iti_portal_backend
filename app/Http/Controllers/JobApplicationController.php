<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobApplicationRequest;
use App\Http\Requests\BatchUpdateStatusRequest;
use App\Services\ApplicationService;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    protected ApplicationService $applicationService;

    protected $firebase;

    public function __construct(ApplicationService $applicationService, FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    
        $this->applicationService = $applicationService;
    }
    /**
     * Display a listing of the user's job applications.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'status' => 'sometimes|in:applied,reviewed,interviewed,hired,rejected',
                'company_id' => 'sometimes|exists:users,id',
            ]);

            $responseData = $this->applicationService->getUserApplications(Auth::id(), $filters);

            if ($responseData['applications']->isEmpty()) {
                return $this->respondWithError('You have no job applications yet', 404);
            }

            return $this->respondWithSuccess($responseData, 'Job applications retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve job applications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created application in storage.
     */
    public function store(JobApplicationRequest $request): JsonResponse
    {
        try {
            // Check if user has already applied for this job
            if ($this->applicationService->hasUserAppliedForJob(Auth::id(), $request->job_id)) {
                return $this->respondWithError('You have already applied for this job.', 422);
            }

            // Check if job is still accepting applications
            $jobValidation = $this->applicationService->isJobAcceptingApplications($request->job_id);
            if (!$jobValidation['valid']) {
                return $this->respondWithError($jobValidation['message'], 422);
            }

            // Create application
            $application = $this->applicationService->createApplication([
                'user_id' => Auth::id(),
                'job_id' => $request->job_id,
                'cover_letter' => $request->cover_letter,
            ], $request->file('cv'));

            // Notify company of new application
            $this->firebase->send( $application->job->company_id, [
                'title' => 'New Job Application Received',
                'type' => 'new_application',
                'body' => auth()->user()->profile->full_name . ' has applied for your job: ' . $application->job->title,
                'sender_id' => Auth::id(),
            ]);

            return $this->respondWithSuccess($application, 'Application submitted successfully.', 201);
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to submit application: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified application.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $application = $this->applicationService->getUserApplication($id, Auth::id());

            if (!$application) {
                return $this->respondWithError('Job application not found', 404);
            }

            return $this->respondWithSuccess($application, 'Job application retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve job application: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified application from storage.
     * Only allows withdrawal if status is still 'applied'
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $application = $this->applicationService->getWithdrawableApplication($id, Auth::id());

            if (!$application) {
                return $this->respondWithError('Job application not found or cannot be withdrawn', 404);
            }

            $this->applicationService->withdrawApplication($application);

            // Notify company of application withdrawal
            $this->firebase->send($application->job->company_id, [
                'title' => 'Job Application Withdrawn',
                'type' => 'application_withdrawn',
                'body' => auth()->user()->profile->full_name . ' has withdrawn their application for the job: ' . $application->job->title,
                'sender_id' => Auth::id(),
            ]);

            return $this->respondWithSuccess([], 'Application withdrawn successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to withdraw application: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display a listing of the applications for a company's job.
     */
    public function companyApplications(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'job_id' => ['sometimes', 'exists:available_jobs,id'],
                'status' => ['sometimes', 'in:applied,reviewed,interviewed,hired,rejected'],
                'include_match_score' => ['sometimes', 'in:true,false,1,0'],
            ]);

            $filters = [];
            if ($request->has('job_id')) {
                $filters['job_id'] = $request->job_id;
            }
            if ($request->has('status')) {
                $filters['status'] = $request->status;
            }
            if ($request->has('include_match_score')) {
                $filters['include_match_score'] = in_array($request->get('include_match_score'), ['true', '1', true, 1]);
            }

            $applications = $this->applicationService->getCompanyApplications(Auth::id(), $filters);

            if ($applications->isEmpty()) {
                return $this->respondWithError('No applications found', 404);
            }

            return $this->respondWithSuccess($applications, 'Applications retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve applications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the status of an application (for companies).
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'status' => ['required', 'in:reviewed,interviewed,hired,rejected'],
                'company_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            ]);

            $application = $this->applicationService->getCompanyApplication($id, Auth::id());

            if (!$application) {
                return $this->respondWithError('Application not found or not accessible', 404);
            }

            $updatedApplication = $this->applicationService->updateApplicationStatus(
                $application,
                $request->status,
                $request->company_notes
            );

            // Notify applicant of status update
            $this->firebase->send($updatedApplication->user_id, [
                'title' => 'Application Status Updated',
                'type' => 'application_status_update',
                'body' => 'Your application for the job "' . $updatedApplication->job->title . '" has been updated to "' . $updatedApplication->status . '".',
                'sender_id' => Auth::id(),
            ]);

            return $this->respondWithSuccess($updatedApplication, 'Application status updated successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to update application status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Download the CV for an application.
     */
    public function downloadCV(string $id)
    {
        try {
            $downloadResult = $this->applicationService->getApplicationForCVDownload($id, Auth::user());

            if (!$downloadResult['success']) {
                $statusCode = $downloadResult['message'] === 'Unauthorized' ? 403 : 404;
                return $this->respondWithError($downloadResult['message'], $statusCode);
            }

            $application = $downloadResult['application'];
            $isCompanyDownload = $downloadResult['is_company_download'];

            // Track CV download by company and auto-update status
            if ($isCompanyDownload) {
                try {
                    $this->applicationService->trackCVDownload($application);
                } catch (\Exception $e) {
                    // Log error but don't prevent download
                    \Log::warning('Failed to update CV download tracking for application ' . $application->id . ': ' . $e->getMessage());
                }
            }

            return Storage::disk('public')->download($application->cv_path);
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to download CV: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Track when a company views an applicant's profile
     */
    public function trackProfileView(string $id): JsonResponse
    {
        try {
            $application = $this->applicationService->getCompanyApplication($id, Auth::id());

            if (!$application) {
                return $this->respondWithError('Application not found or not accessible', 404);
            }

            $trackingData = $this->applicationService->trackProfileView($application);

            return $this->respondWithSuccess($trackingData, 'Profile view tracked successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to track profile view: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update multiple applications status at once (for companies)
     */
    public function batchUpdateStatus(BatchUpdateStatusRequest $request): JsonResponse
    {
        try {
            $result = $this->applicationService->batchUpdateStatus(
                $request->application_ids,
                $request->status,
                Auth::id(),
                $request->company_notes ?? null
            );

            if (!$result['success']) {
                return $this->respondWithError($result['message'], 404);
            }

            return $this->respondWithSuccess([
                'updated_count' => $result['updated_count'],
                'total_requested' => $result['total_requested'],
                'applications' => $result['applications'],
                'message' => $result['message']
            ], 'Batch update completed successfully');

        } catch (\Exception $e) {
            return $this->respondWithError('Failed to update applications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get applications for a job sorted by skill match score
     */
    public function getMatchedApplications(string $jobId): JsonResponse
    {
        try {
            $job = $this->applicationService->getCompanyJob($jobId, Auth::id());

            if (!$job) {
                return $this->respondWithError('Job not found or not accessible', 404);
            }

            $applications = $this->applicationService->getMatchedApplications($job);

            if ($applications->isEmpty()) {
                return $this->respondWithError('No applications found for this job', 404);
            }

            return $this->respondWithSuccess($applications, 'Matched applications retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve matched applications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get job application statistics for a specific job
     */
    public function getJobApplicationStats(string $jobId): JsonResponse
    {
        try {
            $job = $this->applicationService->getCompanyJob($jobId, Auth::id());

            if (!$job) {
                return $this->respondWithError('Job not found or not accessible', 404);
            }

            $stats = $this->applicationService->getJobApplicationStats($job);

            return $this->respondWithSuccess($stats, 'Job application statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve statistics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get user's skill match for a specific job (before applying)
     */
    public function getJobSkillMatch(string $jobId): JsonResponse
    {
        try {
            $result = $this->applicationService->getUserJobMatch(Auth::user(), $jobId);

            if (!$result['success']) {
                $statusCode = $result['message'] === 'Job not found' ? 404 : 422;
                return $this->respondWithError($result['message'], $statusCode);
            }

            return $this->respondWithSuccess($result['match_data'], 'Skill match data retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve skill match data: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the status of an application to 'hired'.
     */
    public function hire(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'company_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            ]);

            $application = $this->applicationService->getCompanyApplication($id, Auth::id());

            if (!$application) {
                return $this->respondWithError('Application not found or not accessible', 404);
            }

            $updatedApplication = $this->applicationService->updateApplicationStatus(
                $application,
                'hired',
                $request->company_notes ?? null
            );

            return $this->respondWithSuccess($updatedApplication, 'Application status updated to hired');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to update application status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the status of an application to 'rejected'.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'company_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            ]);

            $application = $this->applicationService->getCompanyApplication($id, Auth::id());

            if (!$application) {
                return $this->respondWithError('Application not found or not accessible', 404);
            }

            $updatedApplication = $this->applicationService->updateApplicationStatus(
                $application,
                'rejected',
                $request->company_notes ?? null
            );

            return $this->respondWithSuccess($updatedApplication, 'Application status updated to rejected');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to update application status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the status of an application to 'interviewed'.
     */
    public function interview(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'company_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
            ]);

            $application = $this->applicationService->getCompanyApplication($id, Auth::id());

            if (!$application) {
                return $this->respondWithError('Application not found or not accessible', 404);
            }

            $updatedApplication = $this->applicationService->updateApplicationStatus(
                $application,
                'interviewed',
                $request->company_notes ?? null
            );

            return $this->respondWithSuccess($updatedApplication, 'Application status updated to interviewed');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to update application status: ' . $e->getMessage(), 500);
        }
    }
}
