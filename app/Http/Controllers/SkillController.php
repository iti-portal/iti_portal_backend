<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\UserSkill;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function getAllSkillsForUser(Request $request): JsonResponse
    {
        $user_id = $request->user()->id;
        
        try {
            $skills = Skill::join('user_skills', 'skills.id', '=', 'user_skills.skill_id')
                ->where('user_skills.user_id', $user_id)
                ->select('skills.*')
                ->get();

            return $this->responseWithSuccess((new Collection($skills))->toArray(), 'Skills retrieved successfully');
        } catch (\Exception $e) {
            return $this->responseWithError('Failed to retrieve skills', 500);
        }
    }

    public function addSkill(Request $request): JsonResponse
    {
        $user_id = $request->user()->id;
        
        try {
            // Check if request has skill_id
            if ($request->has('skill_id')) {
                $skill_id = $request->input('skill_id');
                $skill = Skill::find($skill_id);
                
                // If skill_id is provided but skill doesn't exist, return error
                if (!$skill) {
                    return $this->responseWithError('Skill not found', 404);
                }
            } 
            // If skill_name is provided, find or create the skill
            else if ($request->has('skill_name')) {
                $skill_name = $request->input('skill_name');
                
                // Find or create the skill
                $skill = Skill::firstOrCreate(
                    ['name' => $skill_name]
                );
                
                $skill_id = $skill->id;
            } else {
                return $this->responseWithError('Either skill_id or skill_name is required', 400);
            }
            
            // Check if skill already exists for user
            if (UserSkill::where('user_id', $user_id)->where('skill_id', $skill_id)->exists()) {
                return $this->responseWithError('Skill already exists for this user', 400);
            }

            UserSkill::create([
                'user_id' => $user_id,
                'skill_id' => $skill_id,
            ]);

            return $this->responseWithSuccess(['skill' => $skill], 'Skill added successfully');
        } catch (\Exception $e) {
            return $this->responseWithError('Failed to add skill: ' . $e->getMessage(), 500);
        }
    }

    public function deleteSkill(Request $request): JsonResponse
    {
        $user_id = $request->user()->id;
        
        try {
            // Check if request has skill_id
            if ($request->has('skill_id')) {
                $skill_id = $request->input('skill_id');
                
                // Delete user skill by skill_id
                $deleted = UserSkill::where('user_id', $user_id)
                    ->where('skill_id', $skill_id)
                    ->delete();
                
                if (!$deleted) {
                    return $this->responseWithError('Skill not found for this user', 404);
                }
            } 
            // If skill_name is provided, find skill by name then delete
            else if ($request->has('skill_name')) {
                $skill_name = $request->input('skill_name');
                
                // Find the skill by name
                $skill = Skill::where('name', $skill_name)->first();
                
                if (!$skill) {
                    return $this->responseWithError('Skill not found with name: ' . $skill_name, 404);
                }
                
                // Delete user skill by skill_id
                $deleted = UserSkill::where('user_id', $user_id)
                    ->where('skill_id', $skill->id)
                    ->delete();
                
                if (!$deleted) {
                    return $this->responseWithError('User does not have this skill', 404);
                }
            } else {
                return $this->responseWithError('Either skill_id or skill_name is required', 400);
            }

            return $this->responseWithSuccess([], 'Skill deleted successfully');
        } catch (\Exception $e) {
            return $this->responseWithError('Failed to delete skill: ' . $e->getMessage(), 500);
        }
    }
}