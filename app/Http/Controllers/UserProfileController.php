<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    /**
     * Perform advanced search and filtering on User Profiles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAndFilter(Request $request)
    {
        try {
            // Use select to only fetch needed fields from the database
            $query = UserProfile::select([
                'user_profiles.id',
                'user_profiles.first_name',
                'user_profiles.last_name',
                'user_profiles.username',
                'user_profiles.phone',
                'user_profiles.track',
                'user_profiles.intake',
                'user_profiles.user_id'
            ]);

            // Search by first name, last name, username, phone
            if ($request->has('search')) {
                $searchTerm = $request->input('search');
                // For better performance, consider using a full-text search index
                // or at least avoid leading wildcards when possible
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('first_name', 'like', $searchTerm . '%') // Using right wildcard is more index-friendly
                      ->orWhere('last_name', 'like', $searchTerm . '%')
                      ->orWhere('phone', 'like', $searchTerm . '%')
                      ->orWhere('username', 'like', $searchTerm . '%');
                });
            }

            // Filter by skill - using join instead of nested whereHas
            if ($request->has('skill')) {
                $skillTerm = $request->input('skill');
                $query->join('user_skills', 'user_profiles.user_id', '=', 'user_skills.user_id')
                      ->join('skills', 'user_skills.skill_id', '=', 'skills.id')
                      ->where('skills.name', 'like', $skillTerm . '%')
                      ->distinct(); // Avoid duplicate results
            }

            // Filter by track
            if ($request->has('track')) {
                $query->where('track', $request->input('track'));
            }

            // Filter by intake
            if ($request->has('intake')) {
                $query->where('intake', $request->input('intake'));
            }

            // Implement pagination
            $perPage = $request->input('per_page', 15); // Default 15 items per page
            $userProfiles = $query->paginate($perPage);

            return $this->responseWithSuccess('User profiles retrieved successfully', $userProfiles);
        } catch (\Exception $e) {
            return $this->responseWithError('An error occurred while searching user profiles: ' . $e->getMessage(), 500);
        }
}
}