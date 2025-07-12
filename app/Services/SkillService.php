<?php

namespace App\Services;

use App\Models\Skill;
use App\Models\UserSkill;
use Illuminate\Support\Collection;

class SkillService
{
    public function getAllSkillsForUser(int $userId): Collection
    {
        return UserSkill::where('user_id', $userId)
            ->with('skill')
            ->get()
            ->pluck('skill');
    }

    public function addSkillToUser(int $userId, array $data)
    {
        if (isset($data['skill_id'])) {
            $skillId = $data['skill_id'];
        } else {
            $skillName = trim($data['skill_name']);
            $skill = Skill::firstOrCreate(['name' => $skillName]);
            $skillId = $skill->id;
        }

        // Check if user already has this skill
        $existingUserSkill = UserSkill::where('user_id', $userId)
            ->where('skill_id', $skillId)
            ->first();

        if ($existingUserSkill) {
            throw new \Exception('Skill already exists for this user');
        }

        return UserSkill::create([
            'user_id' => $userId,
            'skill_id' => $skillId
        ]);
    }

    public function removeSkillFromUser(int $userId, int $skillId): bool
    {
        return UserSkill::where('user_id', $userId)
            ->where('skill_id', $skillId)
            ->delete() > 0;
    }

    public function getAllSkills()
    {
        return Skill::orderBy('name')->get();
    }

    public function searchSkills(string $searchTerm)
    {
        return Skill::where('name', 'like', $searchTerm . '%')
            ->orderBy('name')
            ->limit(10)
            ->get();
    }
}