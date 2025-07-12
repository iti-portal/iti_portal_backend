<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddSkillRequest;
use App\Services\SkillService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    protected $skillService;

    public function __construct(SkillService $skillService)
    {
        $this->skillService = $skillService;
    }

    public function getAllSkillsForUser(Request $request): JsonResponse
    {
        try {
            $skills = $this->skillService->getAllSkillsForUser($request->user()->id);
            
            if ($skills->isEmpty()) {
                // Changed to use the respondWithSuccess method from the parent Controller
                return $this->respondWithSuccess([], 'You Have No Skills Added Yet');
            }

            // Changed to use the respondWithSuccess method
            return $this->respondWithSuccess($skills->toArray(), 'Skills retrieved successfully');
        } catch (\Exception $e) {
            // Changed to use the respondWithError method
            return $this->respondWithError('Failed to retrieve skills', 500);
        }
    }

    public function addSkill(AddSkillRequest $request): JsonResponse
    {
        try {
            $userSkill = $this->skillService->addSkillToUser($request->user()->id, $request->validated());

            // Changed to use the respondWithSuccess method
            return $this->respondWithSuccess($userSkill->load('skill'), 'Skill added successfully');
        } catch (\Exception $e) {
            // Changed to use the respondWithError method
            return $this->respondWithError($e->getMessage(), 400);
        }
    }

    public function deleteSkill(Request $request, int $skillId): JsonResponse
    {
        try {
            $removed = $this->skillService->removeSkillFromUser($request->user()->id, $skillId);

            if (!$removed) {
                // Changed to use the respondWithError method
                return $this->respondWithError('Skill not found for this user', 404);
            }

            // Changed to use the respondWithSuccess method
            return $this->respondWithSuccess([], 'Skill removed successfully');
        } catch (\Exception $e) {
            // Changed to use the respondWithError method
            return $this->respondWithError('Failed to remove skill', 500);
        }
    }

    public function getAllSkills(): JsonResponse
    {
        try {
            $skills = $this->skillService->getAllSkills();
            // Changed to use the respondWithSuccess method
            return $this->respondWithSuccess($skills->toArray(), 'All skills retrieved successfully');
        } catch (\Exception $e) {
            // Changed to use the respondWithError method
            return $this->respondWithError('Failed to retrieve skills', 500);
        }
    }

    public function searchSkills(Request $request): JsonResponse
    {
        try {
            $searchTerm = $request->input('search', '');
            
            if (strlen(trim($searchTerm)) < 2) {
                // Changed to use the respondWithError method
                return $this->respondWithError('Search term must be at least 2 characters', 400);
            }

            $skills = $this->skillService->searchSkills(trim($searchTerm));
            // Changed to use the respondWithSuccess method
            return $this->respondWithSuccess($skills->toArray(), 'Skills found successfully');
        } catch (\Exception $e) {
            // Changed to use the respondWithError method
            return $this->respondWithError('Failed to search skills', 500);
        }
    }
}