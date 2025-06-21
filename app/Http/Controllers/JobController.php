<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Models\AvailableJob;
use App\Models\JobSkill;
use App\Models\Skill;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            return $this->respondWithError('Failed to create job: ' . $e->getMessage(), 500);
        }
    }

    public function show($id)
    {
        try {
            $job = AvailableJob::with(['company', 'job_skills.skill'])->findOrFail($id);
            return $this->respondWithSuccess($job, 'Job retrieved successfully.');
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

            DB::commit();

            return $this->respondWithSuccess($job, 'Job updated successfully');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating job: ' . $e->getMessage());
            return $this->respondWithError('Failed to update job.', 500);
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

            $jobs = $query->paginate((int) $request->get('per_page', 10));
            return $this->respondWithSuccess($jobs, 'Available jobs retrieved successfully');
        } catch (Exception $e) {
            return $this->respondWithError('Failed to load available jobs.', 500);
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
                ->paginate((int) $request->get('per_page', 10));
            return $this->respondWithSuccess($jobs, 'Company jobs retrieved successfully.');

        } catch (Exception $e) {
            return $this->respondWithError('Failed to retrieve your jobs.', 500);
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

            $jobs = $query->paginate((int) $request->get('per_page', 10));

            return $this->respondWithSuccess($jobs, 'All jobs retrieved successfully');
        } catch (Exception $e) {
            return $this->respondWithError('Failed to retrieve jobs: ' . $e->getMessage(), 500);
        }
    }

     public function changeStatus(Request $request, $id): JsonResponse
    {
        try {
            $job = AvailableJob::findOrFail($id);
            $user = Auth::user();

            if ($user->id !== $job->company_id ) {
                return $this->respondWithError('Unauthorized', 403);
            }

            $status = $request->status;
            if (! in_array($status, ['active', 'paused', 'closed'])) {
                return $this->respondWithError('Invalid status.', 422);
            }

            $job->update(['status' => $status]);
            return $this->respondWithSuccess($job, "Job status changed to $status");

        } catch (Exception $e) {
            Log::error('Error changing job status: ' . $e->getMessage());
            return $this->respondWithError('Failed to update job status.', 500);
        }
    }
public function jobsByCompanyId(Request $request, $id): JsonResponse
{
    try {
        $user = Auth::user();

        if (! $user || ! $user->hasRole('admin') && ! $user->hasRole('staff')) {
            return $this->respondWithError('Unauthorized. Only admins or staff can access this.', 403);
        }

        $jobs = AvailableJob::with('job_skills.skill')
            ->where('company_id', $id)
            ->paginate((int) $request->get('per_page', 10));

        return $this->respondWithSuccess($jobs, 'Jobs for the specified company retrieved successfully.');
    } catch (Exception $e) {
        return $this->respondWithError('Failed to retrieve jobs for the company.', 500);
    }
}


public function featuredJobs(Request $request): JsonResponse
{
    try {
        $jobs = AvailableJob::with('company.companyProfile')
            ->where('status', 'active')
            ->where('is_featured', true)
            ->paginate((int) $request->get('per_page', 10));

        return $this->respondWithSuccess($jobs, 'Featured jobs retrieved successfully.');
    } catch (Exception $e) {
        return $this->respondWithError('Failed to retrieve featured jobs.', 500);
    }
}



}
