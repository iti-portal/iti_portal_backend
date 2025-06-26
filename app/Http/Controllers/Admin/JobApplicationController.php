<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of all applications in the system (Admin/Staff only)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => ['sometimes', 'exists:users,id'],
                'company_id' => ['sometimes', 'exists:users,id'],
                'job_id' => ['sometimes', 'exists:available_jobs,id'],
                'status' => ['sometimes', 'in:applied,reviewed,interviewed,hired,rejected'],
                'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
                'sort_by' => ['sometimes', 'in:created_at,applied_at,status'],
                'sort_order' => ['sometimes', 'in:asc,desc'],
            ]);

            $query = JobApplication::with([
                'user.profile',
                'job.company.companyProfile',
                'job' => function($query) {
                    $query->select('id', 'company_id', 'title', 'job_type', 'experience_level');
                }
            ]);

            // Apply filters
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('company_id')) {
                $query->whereHas('job', function($q) use ($request) {
                    $q->where('company_id', $request->company_id);
                });
            }

            if ($request->has('job_id')) {
                $query->where('job_id', $request->job_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $applications = $query->paginate($perPage);

            if ($applications->isEmpty()) {
                return $this->respondWithError('No applications found', 404);
            }

            return $this->respondWithSuccess([
                'applications' => $applications->items(),
                'pagination' => [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                ]
            ], 'Applications retrieved successfully');

        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve applications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified application (Admin/Staff only)
     */
    public function show(string $id): JsonResponse
    {
        try {
            $application = JobApplication::with([
                'user.profile',
                'user.skills',
                'job.company.companyProfile',
                'job.job_skills.skill'
            ])->find($id);

            if (!$application) {
                return $this->respondWithError('Application not found', 404);
            }

            return $this->respondWithSuccess($application, 'Application retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve application: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the status of an application (Admin/Staff only)
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'status' => ['required', 'in:applied,reviewed,interviewed,hired,rejected'],
                'admin_notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
                'notify_parties' => ['sometimes', 'boolean'],
            ]);

            $application = JobApplication::with(['user', 'job.company'])->find($id);

            if (!$application) {
                return $this->respondWithError('Application not found', 404);
            }

            $oldStatus = $application->status;

            DB::beginTransaction();

            $application->update([
                'status' => $request->status,
                'company_notes' => $request->admin_notes ?? $application->company_notes,
            ]);

            // Send notifications if requested
            if ($request->get('notify_parties', true) && $oldStatus !== $request->status) {
                $this->notificationService->notifyApplicantOfStatusChange($application, $oldStatus);
            }

            DB::commit();

            return $this->respondWithSuccess($application, 'Application status updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondWithError('Failed to update application status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified application from storage (Admin/Staff only)
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $application = JobApplication::with(['job'])->find($id);

            if (!$application) {
                return $this->respondWithError('Application not found', 404);
            }

            DB::beginTransaction();

            // Delete CV file if exists
            if ($application->cv_path && Storage::disk('public')->exists($application->cv_path)) {
                Storage::disk('public')->delete($application->cv_path);
            }

            // Decrement applications count
            if ($application->job) {
                $application->job->decrement('applications_count');
            }

            // Delete application
            $application->delete();

            DB::commit();

            return $this->respondWithSuccess([], 'Application deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondWithError('Failed to delete application: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get application statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
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
                'top_companies_by_applications' => JobApplication::select('jobs.company_id', 'users.email as company_email', DB::raw('count(*) as applications_count'))
                    ->join('available_jobs as jobs', 'job_applications.job_id', '=', 'jobs.id')
                    ->join('users', 'jobs.company_id', '=', 'users.id')
                    ->groupBy('jobs.company_id', 'users.email')
                    ->orderByDesc('applications_count')
                    ->limit(10)
                    ->get(),
            ];

            return $this->respondWithSuccess($stats, 'Statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve statistics: ' . $e->getMessage(), 500);
        }
    }
}