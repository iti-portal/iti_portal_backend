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
            if ($skills->isEmpty()) {
            return $this->responseWithSuccess([], 'You Have No Skills Added Yet');
        }

            return $this->responseWithSuccess((new Collection($skills))->toArray(), 'Skills retrieved successfully');
        } catch (\Exception $e) {
            return $this->responseWithError('Failed to retrieve skills', 500);
        }
    }

    public function addSkill(Request $request): JsonResponse
    {
        $user_id = $request->user()->id;
        
        try {
            $validator = \Validator::make($request->all(), [
                'skill_id' => 'sometimes|required_without:skill_name|integer|exists:skills,id',
                'skill_name' => 'sometimes|required_without:skill_id|string|min:2|max:50|regex:/^[a-zA-Z0-9\s]+$/'
            ], [
                'skill_id.exists' => 'Skill not found',
                'skill_name.regex' => 'Skill name can only contain letters, numbers and spaces'
            ]);

            if ($validator->fails()) {
                return $this->responseWithError($validator->errors()->first(), 400);
            }

            if ($request->has('skill_id')) {
                $skill_id = $request->input('skill_id');
                $skill = Skill::find($skill_id);
            } else {
                $skill_name = trim($request->input('skill_name'));
                $skill = Skill::firstOrCreate(
                    ['name' => $skill_name]
                );
                $skill_id = $skill->id;
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

    public function deleteSkill(Request $request, $skill_id): JsonResponse
    {
        $user = $request->user();
        
        try {
            if (!is_numeric($skill_id)) {
                return $this->responseWithError('Invalid skill ID', 400);
            }

            $userSkill = UserSkill::where('user_id', $user->id)
                            ->where('skill_id', $skill_id)
                            ->first();
            
            if (!$userSkill) {
                return $this->responseWithError('Skill not found for this user', 404);
            }
            
            // Allow deletion if user is admin OR owns the skill
            if (!$user->hasRole('admin') && $userSkill->user_id !== $user->id) {
                return $this->responseWithError('Unauthorized to delete this skill', 403);
            }
            
            $userSkill->delete();
            
            return $this->responseWithSuccess([], 'Skill deleted successfully');
        } catch (\Exception $e) {
            return $this->responseWithError('Failed to delete skill: ' . $e->getMessage(), 500);
        }
    }
}
