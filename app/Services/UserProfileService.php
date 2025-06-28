<?php

namespace App\Services;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserProfileService
{
    public function searchAndFilter(Request $request)
    {
        $query = UserProfile::select([
            'user_profiles.id',
            'user_profiles.first_name',
            'user_profiles.last_name',
            'user_profiles.username',
            'user_profiles.phone',
            'user_profiles.track',
            'user_profiles.intake',
            'user_profiles.job_profile',
            'user_profiles.user_id'
        ]);

        if ($request->has('search') && $request->filled('search')) {
            $searchTerm = trim($request->input('search'));
            if (strlen($searchTerm) >= 2) {
                $escapedSearchTerm = str_replace(['%', '\\'], ['\\%', '\\\\'], $searchTerm);
                $query->where(function ($q) use ($escapedSearchTerm) {
                    // Try full text search first if available
                    try {
                        $q->whereRaw("MATCH(job_profile) AGAINST(? IN BOOLEAN MODE)", [$escapedSearchTerm])
                          ->orWhere('first_name', 'like', $escapedSearchTerm . '%')
                          ->orWhere('last_name', 'like', $escapedSearchTerm . '%')
                          ->orWhere('username', 'like', $escapedSearchTerm . '%')
                          ->orWhere('phone', 'like', $escapedSearchTerm . '%');
                    } catch (\Exception $e) {
                        // Fall back to LIKE if full text search fails
                        $q->where('first_name', 'like', $escapedSearchTerm . '%')
                          ->orWhere('last_name', 'like', $escapedSearchTerm . '%')
                          ->orWhere('username', 'like', $escapedSearchTerm . '%')
                          ->orWhere('phone', 'like', $escapedSearchTerm . '%')
                          ->orWhere('job_profile', 'like', $escapedSearchTerm . '%');
                    }
                });
            }
        }

        if ($request->has('skill') && $request->filled('skill')) {
            $skillTerm = $request->input('skill');
            $query->whereExists(function ($subQuery) use ($skillTerm) {
                $subQuery->select(DB::raw(1))
                         ->from('user_skills')
                         ->join('skills', 'user_skills.skill_id', '=', 'skills.id')
                         ->whereColumn('user_skills.user_id', 'user_profiles.user_id')
                         ->where('skills.name', 'like', $skillTerm . '%');
            });
        }

        if ($request->has('track') && $request->filled('track')) {
            $query->where('track', '=', $request->input('track'));
        }

        if ($request->has('intake') && $request->filled('intake')) {
            $query->where('intake', '=', $request->input('intake'));
        }

        if ($request->has('job_profile') && $request->filled('job_profile')) {
            $query->where('job_profile', 'like', '%' . $request->input('job_profile') . '%');
        }

        $query->orderBy('user_profiles.id');
        $perPage = min((int)$request->input('per_page', 15), 100);
        
        return $query->simplePaginate($perPage);
    }
}
