<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Mail\VerifyNewEmail;
use App\Models\Connection;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\UserProfileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail as FacadesMail;
use Illuminate\Support\Facades\URL as FacadesURL;
use Mail;
use Soap\Url;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    protected $userProfileService;

    public function __construct(UserProfileService $userProfileService)
    {
        $this->userProfileService = $userProfileService;
    }
    
    public function getStudents(Request $request){
        $user = $request->user();
        if (!$user){
            return $this->respondWithError('unauthorized', 401);
        }
        try{
            $branch = $user->profile->branch ?? null;
            $intake = $user->profile->intake ?? null;
            $track = $user->profile->track ?? null;
    
            $users = User::with('profile')
                ->where('id', '!=', $request->user()->id)
                ->whereHas('roles', function ($query) {
                    $query->where('name', '=', 'student');
                })
                ->when($branch && $intake && $track, function ($query) use ($branch, $intake, $track) {
                    $query->orderByRaw("CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM user_profiles 
                            WHERE user_profiles.user_id = users.id 
                            AND user_profiles.track = ?
                        ) THEN 0
                        ELSE 1
                    END", [$track]);
    
                    $query->orderByRaw("CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM user_profiles 
                            WHERE user_profiles.user_id = users.id 
                            AND user_profiles.intake = ?
                        ) THEN 0
                        ELSE 1
                    END", [$intake]);
    
                    $query->orderByRaw("CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM user_profiles 
                            WHERE user_profiles.user_id = users.id 
                            AND user_profiles.branch = ?
                        ) THEN 0
                        ELSE 1
                    END", [$branch]);
                })
                ->paginate(10);
    
            return $this->respondWithSuccess(['users' => $users]);
        }catch(\Exception $e){
            $this->respondWithError("Something went wrong", 500);
        }
   }
   public function getGraduates(Request $request){
    $user = $request->user();
    if(!$request->user()){
        return $this->respondWithError('unauthorized', 401);
    }
    try{
        $branch = $user->profile->branch ?? null;
        $intake = $user->profile->intake ?? null;
        $track = $user->profile->track ?? null;

        $users = User::with('profile')
            ->where('id', '!=', $request->user()->id)
            ->whereHas('roles', function ($query) {
                $query->where('name', '=', 'alumni');
            })
            ->when($branch && $intake && $track, function ($query) use ($branch, $intake, $track) {
                $query->orderByRaw("CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM user_profiles 
                        WHERE user_profiles.user_id = users.id 
                        AND user_profiles.track = ?
                    ) THEN 0
                    ELSE 1
                END", [$track]);

                $query->orderByRaw("CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM user_profiles 
                        WHERE user_profiles.user_id = users.id 
                        AND user_profiles.intake = ?
                    ) THEN 0
                    ELSE 1
                END", [$intake]);

                $query->orderByRaw("CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM user_profiles 
                        WHERE user_profiles.user_id = users.id 
                        AND user_profiles.branch = ?
                    ) THEN 0
                    ELSE 1
                END", [$branch]);
            })
            ->paginate(10);

        return $this->respondWithSuccess(['users' => $users]);
        }catch(\Exception $e){
            return$this->respondWithError("Something went wrong", 500);
        }
   }
   public function getAllItians(Request $request){
    $user = $request->user();
    if(!$request->user()){
        return $this->respondWithError('unauthorized', 401);
    }
    try{
        $branch = $user->profile->branch ?? null;
        $intake = $user->profile->intake ?? null;
        $track = $user->profile->track ?? null;

        $connections = Connection::where('requester_id', $user->id)
            ->orWhere('addressee_id', $user->id)
            ->where('status', 'accepted')
            ->get()
            ->map(function ($connection) use($user) {
                return $connection->addressee_id ==$user->id ? $connection->requester_id : $connection->addressee_id;
            })->unique()->all();

        
        $users = User::with('profile')
            ->where('id', '!=', $request->user()->id)
            ->whereNotIn('id', $connections)
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['student', 'alumni']);
            })->whereNotIn('id', $connections)
            ->when($branch && $intake && $track, function ($query) use ($branch, $intake, $track) {
                $query->orderByRaw("CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM user_profiles 
                        WHERE user_profiles.user_id = users.id 
                        AND user_profiles.track = ?
                    ) THEN 0
                    ELSE 1
                END", [$track]);

                $query->orderByRaw("CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM user_profiles 
                        WHERE user_profiles.user_id = users.id 
                        AND user_profiles.intake = ?
                    ) THEN 0
                    ELSE 1
                END", [$intake]);

                $query->orderByRaw("CASE 
                    WHEN EXISTS (
                        SELECT 1 FROM user_profiles 
                        WHERE user_profiles.user_id = users.id 
                        AND user_profiles.branch = ?
                    ) THEN 0
                    ELSE 1
                END", [$branch]);
            })

            ->paginate(10);

        return $this->respondWithSuccess(['users' => $users]);
        }catch(\Exception $e){
            $this->respondWithError("Something went wrong", 500);
        }
   }
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

    
    public function updateUserProfile(UpdateProfileRequest $request){
        $user = $request->user();
        DB::beginTransaction();
        try {
            if(!$user){
                return $this->respondWithError('User not found', 404);
            }
            $profile = $user->profile;
            if(!$profile){
                return $this->respondWithError('User profile not found', 404);
            }
 
            if ($request->filled('email') && $request->email !== $user->email) {
                $user->new_email = $request->email;
                $signedUrl = FacadesURL::temporarySignedRoute(
                    'verify-new-email',
                    now()->addHours(24),
                    ['user' => $user->id]
                );
                FacadesMail::to($user->new_email)->send(new VerifyNewEmail($user,$signedUrl));
            }
            if($request->hasFile('profile_picture')) {
                $image = $request->file('profile_picture');
                $path = $image->store('profile_images', 'public');
                $profile->profile_picture = $path;
            }
            $profile->first_name = $request->first_name ?? $profile->first_name;
            $profile->last_name = $request->last_name ?? $profile->last_name;   
            $profile->phone = $request->phone ?? $profile->phone;

            if($request->filled('student_status')&& $request->student_status === 'current' && $profile->student_status === 'graduate'){
                return $this->respondWithError('You are not a current student', 400);
            }
            $profile->available_for_freelance = $request->available_for_freelance ?? $profile->available_for_freelance;
            $profile->summary = $request->summary ?? $profile->summary;
            $profile->portfolio_url = $request->portfolio_url ?? $profile->portfolio_url;
            $profile->whatsapp = $request->whatsapp ?? $profile->whatsapp;
            $profile->linkedin = $request->linkedin ?? $profile->linkedin;
            $profile->github = $request->github ?? $profile->github;
            $profile->job_profile = $request->job_profile ?? $profile->job_profile;
            
            $profile->username = $request->username ?? $profile->username;
            
            $user->save();
            $profile->save();
            DB::commit();
            return $this->respondWithSuccess([
                'user' => $user->makeHidden(['password', 'remember_token']),
            ]);           
            
            }catch (\Exception $e) {
            DB::rollBack();
            return $this->respondWithError($e->getMessage(), 500);
        }
    }
    
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
