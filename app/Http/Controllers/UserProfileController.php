<?php
namespace App\Http\Controllers;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

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

            // Apply same filters as above...
            if ($request->has('search') && $request->filled('search')) {
                $searchTerm = trim($request->input('search'));
                if (strlen($searchTerm) >= 2) {
                    $escapedSearchTerm = str_replace(['%', '\\'], ['\%', '\\\\'], $searchTerm);
                    $query->where(function ($q) use ($escapedSearchTerm) {
                        $q->where('first_name', 'like', $escapedSearchTerm . '%')
                          ->orWhere('last_name', 'like', $escapedSearchTerm . '%')
                          ->orWhere('username', 'like', $escapedSearchTerm . '%')
                          ->orWhere('phone', 'like', $escapedSearchTerm . '%');
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

            $query->orderBy('user_profiles.id');
            $perPage = min((int)$request->input('per_page', 15), 100);
            
            // Use simplePaginate - much faster, no total count
            $paginatedResults = $query->simplePaginate($perPage);
            
            return response()->json([
                'success' => true,
                'message' => 'User profiles retrieved successfully',
                'data' => $paginatedResults->items(),
                'meta' => [
                    'current_page' => $paginatedResults->currentPage(),
                    'per_page' => $paginatedResults->perPage(),
                    'has_more_pages' => $paginatedResults->hasMorePages()
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->responseWithError('An error occurred while searching user profiles: ' . $e->getMessage(), 500);
        }
    }
    }