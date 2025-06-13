<?php
namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkExperienceRequest;
use App\Http\Requests\UpdateWorkExperienceRequest;
use App\Models\User;
use App\Models\WorkExperience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkExperienceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return $this->respondWithError('User not authenticated', 401);
        }
        try {
            $workExperiences = $user->workExperiences()->get();
            if ($workExperiences->isEmpty()) {
                return $this->respondWithError('You have no work experiences added yet', 404);
            }
            return $this->respondWithSuccess($workExperiences, 'Work experiences retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve work experiences', 500);
        }
    }
    public function store(StoreWorkExperienceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user      = Auth::user();
        if (! $user) {
            return $this->respondWithError('User not authenticated', 401);
        }
        if (isset($validated['is_current']) && $validated['is_current'] === true) {
            $validated['end_date'] = null;
        }
        try {
            $experience = $user->workExperiences()->create($validated);
            return $this->respondWithSuccess($experience, 'Work experience added successfully', 201);

        } catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }

    }

    public function update(UpdateWorkExperienceRequest $request, WorkExperience $workExperience): JsonResponse
    {
        $validated = $request->validated();
        $user      = Auth::user();
        if ($workExperience->user_id !== $user->id) {
            return $this->respondWithError('Unauthorized to update this work experience', 403);
        }
        try {
            if (isset($validated['is_current']) && $validated['is_current'] === true) {
                $validated['end_date'] = null;
            }
            $workExperience->update($validated);
            return $this->respondWithSuccess($workExperience, 'Work experience updated successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to update work experience', 500);
        }
    }
    public function destroy(WorkExperience $workExperience): JsonResponse
    {
        $user = Auth::user();
        if ($workExperience->user_id !== $user->id) {
            return $this->respondWithError('Unauthorized to delete this work experience', 403);
        }
        try {
            $workExperience->delete();
            return $this->respondWithSuccess([], 'Work experience deleted successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to delete work experience', 500);
        }
    }
    public function showUserExperiences(User $user): JsonResponse
    {
        try {
            $workExperiences = $user->workExperiences()->get();
            if ($workExperiences->isEmpty()) {
                return $this->respondWithError('This user has no work experiences', 404);
            }
            return $this->respondWithSuccess($workExperiences, 'User work experiences retrieved successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('Failed to retrieve user work experiences', 500);
        }
    }
}
