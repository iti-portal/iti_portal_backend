<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Services\NotificationService;
use App\Services\AdminApplicationService;
use App\Http\Requests\Admin\AdminJobApplicationIndexRequest;
use App\Http\Requests\Admin\AdminJobApplicationUpdateStatusRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    protected NotificationService $notificationService;
    protected AdminApplicationService $adminApplicationService;

    public function __construct(NotificationService $notificationService, AdminApplicationService $adminApplicationService)
    {
        $this->notificationService = $notificationService;
        $this->adminApplicationService = $adminApplicationService;
    }

    /**
     * Display a listing of all applications in the system (Admin/Staff only)
     */
    public function index(AdminJobApplicationIndexRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $perPage = $validated['per_page'] ?? 10; // Use a default value, e.g., 10, if not provided
            unset($validated['per_page']); // Remove per_page from filters if it's not a filter parameter
            $applications = $this->adminApplicationService->getAllApplications($validated, $perPage);

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
            $application = $this->adminApplicationService->getApplicationById($id);

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
    public function updateStatus(AdminJobApplicationUpdateStatusRequest $request, string $id): JsonResponse
    {
        try {
            \Log::info('UpdateStatus called with ID: ' . $id);
            \Log::info('Request data: ', $request->all());
            
            $validated = $request->validated();
            $application = JobApplication::findOrFail($id);
            $updatedApplication = $this->adminApplicationService->updateApplicationStatus(
                $application,
                $validated['status'],
                $validated['admin_notes'] ?? null,
                $validated['notify_parties'] ?? false
            );

            return $this->respondWithSuccess($updatedApplication, 'Application status updated successfully');
        } catch (\Exception $e) {
            \Log::error('UpdateStatus error: ' . $e->getMessage());
            return $this->respondWithError('Failed to update application status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified application from storage (Admin/Staff only)
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->adminApplicationService->deleteApplication($id);

            return $this->respondWithSuccess([], 'Application deleted successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to delete application: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get application statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->adminApplicationService->getApplicationStatistics();

            return $this->respondWithSuccess($stats, 'Application statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve application statistics: ' . $e->getMessage(), 500);
        }
    }
}
