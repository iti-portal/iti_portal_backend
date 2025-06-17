<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobApplicationRequest;
use App\Http\Requests\UpdateCVRequest;
use App\Models\AvailableJob;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class JobApplicationController extends Controller
{
    /**
     * Display a listing of the user's job applications.
     */
    public function index(): JsonResponse
    {
        try {
            $applications = JobApplication::with('job.company.companyProfile')
                ->where('user_id', Auth::id())
                ->latest()
                ->get();

            if ($applications->isEmpty()) {
                return $this->respondWithError('You have no job applications yet', 404);
            }

            return $this->respondWithSuccess($applications, 'Job applications retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve job applications: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created application in storage.
     */
    public function store(JobApplicationRequest $request): JsonResponse
    {
        // Check if user has already applied for this job
        $existingApplication = JobApplication::where('user_id', Auth::id())
            ->where('job_id', $request->job_id)
            ->first();

        if ($existingApplication) {
            return $this->respondWithError('You have already applied for this job.', 422);
        }

        // Check if job is still accepting applications
        try {
            $job = AvailableJob::findOrFail($request->job_id);

            if ($job->status !== 'active' || $job->application_deadline < now()) {
                return $this->respondWithError('This job is no longer accepting applications.', 422);
            }

            DB::beginTransaction();

            // Handle CV upload
            $cvPath = $request->file('cv')->store('cv-documents', 'public');

            // Create application
            $application = JobApplication::create([
                'user_id' => Auth::id(),
                'job_id' => $request->job_id,
                'cover_letter' => $request->cover_letter,
                'cv_path' => $cvPath,
                'status' => 'applied',
                'applied_at' => now(),
            ]);

            // Increment applications count
            $job->increment('applications_count');

            DB::commit();

            return $this->respondWithSuccess($application, 'Application submitted successfully.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondWithError('Failed to submit application: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified application.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $application = JobApplication::with(['job.company.companyProfile'])
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->first();

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
            $application = JobApplication::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'applied')
                ->first();

            if (!$application) {
                return $this->respondWithError('Job application not found or cannot be withdrawn', 404);
            }

            DB::beginTransaction();

            // Delete CV file
            if ($application->cv_path) {
                Storage::disk('public')->delete($application->cv_path);
            }

            // Decrement applications count
            $application->job->decrement('applications_count');

            // Delete application
            $application->delete();

            DB::commit();

            return $this->respondWithSuccess([], 'Application withdrawn successfully');
        } catch (\Exception $e) {
            DB::rollBack();
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
            ]);

            // Get jobs posted by this company
            $companyId = Auth::id();

            $query = JobApplication::with(['user.profile', 'job'])
                ->whereHas('job', function($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                });

            // Filter by job if provided
            if ($request->has('job_id')) {
                $query->where('job_id', $request->job_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $applications = $query->latest()->get();

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

            $companyId = Auth::id();

            $application = JobApplication::with('job')
                ->whereHas('job', function($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })
                ->find($id);

            if (!$application) {
                return $this->respondWithError('Application not found or not accessible', 404);
            }

            $application->update([
                'status' => $request->status,
                'company_notes' => $request->company_notes,
            ]);

            return $this->respondWithSuccess($application, 'Application status updated successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to update application status: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the CV for a job application.
     * Only allows updating if the application status is still 'applied'
     */
    public function updateCV(UpdateCVRequest $request, string $id): JsonResponse
    {
        try {
            $application = JobApplication::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'applied')
                ->first();

            if (!$application) {
                return $this->respondWithError('Job application not found or cannot be updated. You can only update CV for applications that haven\'t been processed yet.', 404);
            }

            DB::beginTransaction();

            // Delete old CV file if it exists
            if ($application->cv_path && Storage::disk('public')->exists($application->cv_path)) {
                Storage::disk('public')->delete($application->cv_path);
            }

            // Upload new CV file
            $newCvPath = $request->file('cv')->store('cv-documents', 'public');

            // Update application with new CV path
            $application->update([
                'cv_path' => $newCvPath,
            ]);

            DB::commit();

            return $this->respondWithSuccess($application, 'CV updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Clean up uploaded file if something went wrong
            if (isset($newCvPath) && Storage::disk('public')->exists($newCvPath)) {
                Storage::disk('public')->delete($newCvPath);
            }
            
            return $this->respondWithError('Failed to update CV: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Download the CV for an application.
     */
    public function downloadCV(string $id)
    {
        try {
            $application = null;

            // For students/alumni - their own applications
            if (Auth::user()->hasRole(['student', 'alumni'])) {
                $application = JobApplication::where('id', $id)
                    ->where('user_id', Auth::id())
                    ->first();

                if (!$application) {
                    return $this->respondWithError('Application not found', 404);
                }
            }
            // For companies - applications to their jobs
            elseif (Auth::user()->hasRole('company')) {
                $companyId = Auth::id();

                $application = JobApplication::whereHas('job', function($query) use ($companyId) {
                    $query->where('company_id', $companyId);
                })
                ->find($id);

                if (!$application) {
                    return $this->respondWithError('Application not found or not accessible', 404);
                }
            } else {
                return $this->respondWithError('Unauthorized', 403);
            }

            if (!$application->cv_path || !Storage::disk('public')->exists($application->cv_path)) {
                return $this->respondWithError('CV file not found', 404);
            }

            return Storage::disk('public')->download($application->cv_path);
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to download CV: ' . $e->getMessage(), 500);
        }
    }
}
