<?php

namespace App\Services;

use App\Models\User;
use App\Models\AvailableJob;
use App\Models\JobApplication;
use Illuminate\Support\Collection;

class SkillMatchingService
{
    /**
     * Calculate skill match score between a user and a job
     */
    public function calculateMatchScore(User $user, AvailableJob $job): array
    {
        $userSkills = $user->skills()->pluck('skills.id', 'skills.name')->toArray();
        $jobSkills = $job->job_skills()->with('skill')->get();

        if ($jobSkills->isEmpty()) {
            return [
                'match_score' => 0,
                'total_skills' => 0,
                'matched_skills' => 0,
                'required_skills_matched' => 0,
                'required_skills_total' => 0,
                'matched_skill_names' => [],
                'missing_skill_names' => [],
                'missing_required_skills' => [],
            ];
        }

        $totalSkills = $jobSkills->count();
        $requiredSkills = $jobSkills->where('is_required', true);
        $requiredSkillsTotal = $requiredSkills->count();

        $matchedSkills = [];
        $missingSkills = [];
        $missingRequiredSkills = [];
        $requiredSkillsMatched = 0;

        foreach ($jobSkills as $jobSkill) {
            $skillName = $jobSkill->skill->name;
            $skillId = $jobSkill->skill->id;

            if (in_array($skillId, $userSkills)) {
                $matchedSkills[] = $skillName;
                if ($jobSkill->is_required) {
                    $requiredSkillsMatched++;
                }
            } else {
                $missingSkills[] = $skillName;
                if ($jobSkill->is_required) {
                    $missingRequiredSkills[] = $skillName;
                }
            }
        }

        $matchedSkillsCount = count($matchedSkills);
        
        // Calculate match score (weighted towards required skills)
        $requiredWeight = 0.7;
        $optionalWeight = 0.3;
        
        $requiredScore = $requiredSkillsTotal > 0 ? ($requiredSkillsMatched / $requiredSkillsTotal) : 1;
        $optionalSkillsTotal = $totalSkills - $requiredSkillsTotal;
        $optionalSkillsMatched = $matchedSkillsCount - $requiredSkillsMatched;
        $optionalScore = $optionalSkillsTotal > 0 ? ($optionalSkillsMatched / $optionalSkillsTotal) : 1;
        
        $matchScore = ($requiredScore * $requiredWeight) + ($optionalScore * $optionalWeight);
        $matchScore = round($matchScore * 100, 2); // Convert to percentage

        return [
            'match_score' => $matchScore,
            'total_skills' => $totalSkills,
            'matched_skills' => $matchedSkillsCount,
            'required_skills_matched' => $requiredSkillsMatched,
            'required_skills_total' => $requiredSkillsTotal,
            'matched_skill_names' => $matchedSkills,
            'missing_skill_names' => $missingSkills,
            'missing_required_skills' => $missingRequiredSkills,
        ];
    }

    /**
     * Get applications with match scores for a job
     */
    public function getApplicationsWithMatchScores(AvailableJob $job): Collection
    {
        $applications = $job->applications()->with(['user.skills', 'user.profile'])->get();

        return $applications->map(function ($application) use ($job) {
            $matchData = $this->calculateMatchScore($application->user, $job);
            $application->match_data = $matchData;
            return $application;
        })->sortByDesc('match_data.match_score');
    }

    /**
     * Get user's skill match for a specific job (before applying)
     */
    public function getUserJobMatch(User $user, AvailableJob $job): array
    {
        return $this->calculateMatchScore($user, $job);
    }
}