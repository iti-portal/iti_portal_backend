<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\AvailableJob;
use App\Models\JobSkill;
use App\Models\Skill;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function createJob(StoreJobRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (! $user->hasRole('company')) {
                return $this->respondWithError('Unauthorized to create jobs', 403);
            }

            DB::beginTransaction();
            $jobData               = $request->validated();
            $jobData['company_id'] = $user->id;
            $jobData['status']     = 'active';

            $job = AvailableJob::create($jobData);

            foreach ($request->input('skills', []) as $skillData) {
                $skill = Skill::firstOrCreate([
                    'name' => strtolower(trim($skillData['name'])),
                ]);
                JobSkill::firstOrCreate(
                    [
                        'job_id'      => $job->id,
                        'skill_id'    => $skill->id,
                        'is_required' => $skillData['is_required'] ?? true,
                    ]
                );

            }
            $job->load('job_skills.skill');
            DB::commit();

            return $this->respondWithSuccess(['job' => $job], 'Job created successfully');

        } catch (Exception $e) {
            DB::rollBack();
            return $this->respondWithError('Failed to create job: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {

            $job = AvailableJob::with(['company', 'job_skills.skill'])->findOrFail($id);
            return $this->respondWithSuccess($job, 'Job retrieved successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->respondWithError('Job not found.', 404);
        } catch (Exception $e) {
            return $this->respondWithError('Failed to retrieve job: ' . $e->getMessage(), 500);
        }

    }
    public function updateJob(UpdateJobRequest $request, $id)
    {
        try {
            $user = Auth::user();
            $job  = AvailableJob::findOrFail($id);
            if ($user->id !== $job->company_id) {
                return $this->respondWithError('Unauthorized', 403);

            }
            DB::beginTransaction();

            $job->update($request->validated());

            if ($request->has('skills')) {
                $newSkills   = $request->input('skills', []);
                $newSkillIds = [];

                foreach ($newSkills as $skillData) {
                    $skill = Skill::firstOrCreate(['name' => strtolower(trim($skillData['name']))]);

                    $newSkillIds[] = $skill->id;

                    JobSkill::updateOrCreate(
                        ['job_id' => $job->id, 'skill_id' => $skill->id],
                        ['is_required' => $skillData['is_required'] ?? true]
                    );
                }

                // Remove skills that are no longer in the request
                JobSkill::where('job_id', $job->id)->whereNotIn('skill_id', $newSkillIds)->delete();
                $job->load('job_skills.skill');
            }
            DB::commit();

            return $this->respondWithSuccess($job, 'Job updated successfully');

        } catch (ModelNotFoundException $e) {
            return $this->respondWithError('Job not found.', 404);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->respondWithError('Failed to update job.' . $e->getMessage(), 500);
        }
    }
    public function destroy($id)
    {
        try {
            $job  = AvailableJob::findOrFail($id);
            $user = Auth::user();
            if ($user->id !== $job->company_id && ! $user->hasRole('admin')) {
                return $this->respondWithError('Unauthorized', 403);
            }
            $job->delete();
            return $this->respondWithSuccess(null, 'Job deleted successfully.');

        } catch (ModelNotFoundException $e) {
            return $this->respondWithError('Job not found.', 404);
        } catch (Exception $e) {
            return $this->respondWithError('Failed to delete job: ' . $e->getMessage(), 500);
        }

    }
    public function availableJobs(Request $request)
    {
        try {
            $query = AvailableJob::with('company.companyProfile')
                ->where('status', 'active');
            if ($request->filled('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }
            if ($request->filled('location')) {
                $query->whereHas('company.companyProfile', function ($q) use ($request) {
                    $q->where('location', 'like', '%' . $request->location . '%');
                });
            }

            $jobs = $query->simplePaginate((int) $request->get('per_page', 10));
            return $this->respondWithSuccess($jobs, 'Available jobs retrieved successfully');
        } catch (Exception $e) {
            return $this->respondWithError('Failed to load available jobs.' . $e->getMessage(), 500);
        }
    }
    public function companyJobs(Request $request)
    {
        try {
            $user = Auth::user();

            if (! $user->hasRole('company')) {
                return $this->respondWithError('Unauthorized.', 403);
            }

            $jobs = AvailableJob::with('job_skills.skill')
                ->where('company_id', $user->id)
                ->when($request->has('status'), function ($query) use ($request) {
                    $query->where('status', $request->status);
                })
                ->orderBy('created_at', 'desc')
                ->simplePaginate((int) $request->get('per_page', 10));
            return $this->respondWithSuccess($jobs, 'Company jobs retrieved successfully.');

        } catch (Exception $e) {
            return $this->respondWithError('Failed to retrieve your jobs.' . $e->getMessage(), 500);
        }
    }
    public function adminJobs(Request $request): JsonResponse
    {
        try {
            $query = AvailableJob::with(['company', 'job_skills.skill']);

            if ($request->filled('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }

            if ($request->filled('company_name')) {
                $query->whereHas('company.companyProfile', function ($q) use ($request) {
                    $q->where('company_name', 'like', '%' . $request->company_name . '%');
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $query->orderByDesc('created_at');

            $jobs = $query->simplePaginate((int) $request->get('per_page', 10));

            return $this->respondWithSuccess($jobs, 'All jobs retrieved successfully');
        } catch (Exception $e) {
            return $this->respondWithError('Failed to retrieve jobs: ' . $e->getMessage(), 500);
        }
    }

    public function changeStatus(Request $request, $id): JsonResponse
    {
        try {
            $job  = AvailableJob::findOrFail($id);
            $user = Auth::user();

            if ($user->id !== $job->company_id) {
                return $this->respondWithError('Unauthorized', 403);
            }

            $status = $request->status;
            if (! $this->isValidStatus($status)) {
                return $this->respondWithError('Invalid status.', 422);
            }

            $job->update(['status' => $status]);
            return $this->respondWithSuccess($job, "Job status changed to $status");

        } catch (ModelNotFoundException $e) {
            return $this->respondWithError('Job not found.', 404);
        } catch (Exception $e) {

            return $this->respondWithError('Failed to update job status.' . $e->getMessage(), 500);
        }
    }
    public function jobsByCompanyId(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            if (! $user || ! $user->hasRole(['admin', 'staff'])) {
                return $this->respondWithError('Unauthorized. Only admins or staff can access this.', 403);
            }
            $company = User::where('id', $id)->whereHas('roles', function ($query) {
                $query->where('name', 'company');
            })->first();

            if (! $company) {
                return $this->respondWithError('Company not found.', 404);
            }
            // Fetch jobs for the specified company
            $jobs = AvailableJob::with('job_skills.skill')
                ->where('company_id', $id)
                ->simplePaginate((int) $request->get('per_page', 10));

            return $this->respondWithSuccess($jobs, 'Jobs for the specified company retrieved successfully.');
        } catch (Exception $e) {
            return $this->respondWithError('Failed to retrieve jobs for the company.' . $e->getMessage(), 500);
        }
    }

    public function jobsByCompanyIdForUsers(Request $request, $id): JsonResponse
    {
        try {

            $company = User::where('id', $id)->whereHas('roles', function ($query) {
                $query->where('name', 'company');
            })->first();
            if (! $company) {
                return $this->respondWithError('Company not found.', 404);
            }
            $jobs = AvailableJob::with('company.companyProfile', 'job_skills.skill')
                ->where('company_id', $id)
                ->where('status', 'active')
                ->simplePaginate((int) $request->get('per_page', 10));

            return $this->respondWithSuccess($jobs, 'Jobs for the specified company retrieved successfully.');
        } catch (Exception $e) {
            return $this->respondWithError('Failed to retrieve jobs for the company.' . $e->getMessage(), 500);
        }
    }

    public function featuredJobs(Request $request): JsonResponse
    {
        try {
            $jobs = AvailableJob::with('company.companyProfile', 'job_skills.skill')
                ->where('status', 'active')
                ->where('is_featured', true)
                ->simplePaginate((int) $request->get('per_page', 10));

            return $this->respondWithSuccess($jobs, 'Featured jobs retrieved successfully.');
        } catch (Exception $e) {
            return $this->respondWithError('Failed to retrieve featured jobs.' . $e->getMessage(), 500);
        }
    }
    private function isValidStatus($status): bool
    {
        return in_array($status, ['active', 'paused', 'closed']);
    }

}
