<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

class UserProfileController extends Controller
{
    //
    private function getProfileData(User $user){
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        try{
        $profile = $user->load('profile',
            'workExperiences',
            'educations',
            'projects',
            'skills',
            'awards',
            'certificates'
        );
       
        if(!$user->profile){
            return $this->respondWithError('User profile not found', 404);
        } 
        $user->profile->makeHidden([
            'nid_front_image',
            'nid_back_image',
        ]);

        return $this->respondWithSuccess([
            'user' => $user->makeHidden(['password', 'remember_token']),
        ],
    
    'User profile retrieved successfully');
    }catch (\Exception $e) {
        $this->respondWithError("Something went wrong", 500);
    }

    }
    public function getUserProfile(Request $request)
    {
        $user = $request->user();
        return $this->getProfileData($user);
        
    }
    public function getUserProfileById(Request $request, $id){
        try {
            $user = User::findOrFail($id);
            return $this->getProfileData($user);
        } catch (\Exception $e) {
            return $this->respondWithError('User not found', 404);
        }
    }

    
    // public function updateUserProfile(UpdateProfileRequest $request){
    //     $user = $request->user();
    //     try {
    //         if(!$user){
    //             return $this->respondWithError('User not found', 404);
    //         }
    //         $profile = $user->profile;
    //         if(!$profile){
    //             return $this->respondWithError('User profile not found', 404);
    //         }
            
    //         if (isset($request->graduation_date) && $request->graduation_date > now()) {
    //             return $this->respondWithError('Graduation date cannot be in the future', 400);
    //         }
    //         $profile->update(
    //             $request->only([
    //                 'username',
    //                 'email',
    //                 'password',
    //                 'first_name',
    //                 'last_name',
    //                 'phone',
    //                 'governorate',
    //                 'track',
    //                 'intake',
    //                 'graduation_date',
    //                 'student_status',
    //                 'username',
    //                 'summery',
    //                 'whatsapp',
    //                 'linkedin',
    //                 'github',
    //                 'portfolio_url',
    //                 'governorate',
    //                 'student_status',
    //                 // 'graduation_date',
    //                 // 'track',
    //                 // 'intake',
    //             ])
    //             );
            
    //     }catch (\Exception $e) {
    //         return $this->respondWithError($e->getMessage(), 500);
    //     }
    // }
    public function deleteUserProfile(Request $request)
    {
        $user = $request->user();
        try {
            if(!$user){
                return $this->respondWithError('User not found', 404);
            }
            $profile = $user->profile;
            if(!$profile){
                return $this->respondWithError('User profile not found', 404);
            }
            $user->delete();
            return $this->respondWithSuccess([], 'User profile deleted successfully');
        }catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    }
    public function deleteUserProfileById(Request $request, $id){
        try {
            $user = User::findOrFail($id);
            $profile = $user->profile;
            if(!$profile){
                return $this->respondWithError('User profile not found', 404);
            }
            $user->delete();
            return $this->respondWithSuccess([], 'User profile deleted successfully');
        } catch (\Exception $e) {
            return $this->respondWithError('User not found', 404);
        }
    }


    public function updateUserProfileImage(Request $request)
    {
        $user = $request->user();
        try {
            if(!$user){
                return $this->respondWithError('User not found', 404);
            }
            $profile = $user->profile;
            if(!$profile){
                return $this->respondWithError('User profile not found', 404);
            }
            $request->validate(['profile_picture' => 'required|image|mimes:jpeg,png,jpg,svg|max:2048']);
                $image = $request->file('profile_picture');
                $path = $image->store('profile_images', 'public');
                $profile->update(['profile_picture' => $path]);
            
            return $this->respondWithSuccess(['profile_picture'=> $profile->profile_picture], 'Profile image updated successfully');
        }catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    }
    public function updateUserCoverPhoto(Request $request){
        $user = $request->user();
        try {
            if(!$user){
                return $this->respondWithError('User not found', 404);
            }
            $profile = $user->profile;
            if(!$profile){
                return $this->respondWithError('User profile not found', 404);
            }
            $request->validate(['cover_photo' => 'required|image|mimes:jpeg,png,jpg,svg,webp|max:2048|dimensions:min_width=400,min_height=400']);
            $image = $request->file('cover_photo');
            $path = $image->store('cover_photos', 'public');
            $profile->update(['cover_photo' => $path]);
            return $this->respondWithSuccess(['cover_photo'=> $profile->cover_photo], 'Cover photo updated successfully');
        }catch (\Exception $e) {
            return $this->respondWithError($e->getMessage(), 500);
        }
    } /**
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
            return $this->respondWithError('An error occurred while searching user profiles: ' . $e->getMessage(), 500);
        }
    }
    }
