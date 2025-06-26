<?php
namespace App\Http\Controllers;

use App\Models\UserProfile;
use App\Services\UserProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    protected $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }

    /**
     * Perform advanced search and filtering on User Profiles.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAndFilter(Request $request)
    {
        try {
            $paginatedResults = $this->userProfileService->searchAndFilter($request);
            
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
            return $this->respondWithError('An error occurred while searching user profiles: ' . $e->getMessage(), 500);
        }
    }
}
