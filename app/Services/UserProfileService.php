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

        if ($request->has('track') && $request->filled('track')) {
            $query->where('track', '=', $request->input('track'));
        }

        if ($request->has('intake') && $request->filled('intake')) {
            $query->where('intake', '=', $request->input('intake'));
        }

        if ($request->has('program') && $request->filled('program')) {
            $query->where('program', '=', $request->input('program'));
        }

        if ($request->has('job_profile') && $request->filled('job_profile')) {
            $query->where('job_profile', 'like', '%' . $request->input('job_profile') . '%');
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

        $users = $query->get();

        if ($request->has('search') && $request->filled('search')) {
            $searchTerm = strtolower($request->input('search'));
            $users = $users->filter(function ($user) use ($searchTerm) {
                return levenshtein(strtolower($user->first_name), $searchTerm) <= 2 ||
                       levenshtein(strtolower($user->last_name), $searchTerm) <= 2 ||
                       levenshtein(strtolower($user->username), $searchTerm) <= 2;
            });
        }
        
        $perPage = min((int)$request->input('per_page', 15), 100);
        $page = $request->input('page', 1);
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $users->forPage($page, $perPage),
            $users->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return $paginated;
    }
}
