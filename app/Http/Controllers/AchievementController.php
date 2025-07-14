<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAchievementRequest;
use App\Http\Requests\UpdateAchievementRequest;
use App\Models\Achievement;
use App\Models\Award;
use App\Models\Certificate;
use App\Models\Connection;
use App\Models\Project;
use App\Models\WorkExperience;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Log;

use function PHPUnit\Framework\returnSelf;

class AchievementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        try{
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            
            $achievements = Achievement::with([
                'user.profile:id,user_id,first_name,last_name,profile_picture',
                'comments.user.profile:id,user_id,first_name,last_name,profile_picture',
                'likes.user.profile:id,user_id,first_name,last_name,profile_picture',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
            
            $achievementsData = $achievements->getCollection()->map(function ($achievement)use($user) {
                $likedByUser = $achievement->likes->contains('user_id', $user->id);

                
                return [
                    'id' => $achievement->id,
                    'user_id' => $achievement->user_id,
                    'type' => $achievement->type,
                    'title' => $achievement->title,
                    'description' => $achievement->description,
                    'like_count' => $achievement->like_count,
                    'comment_count' => $achievement->comment_count,
                    'is_liked' => $likedByUser,
                    'created_at' => $achievement->created_at,
                    'user_profile' => optional($achievement->user->profile)->only(['first_name', 'last_name', 'profile_picture']),
                    'comments' => $achievement->comments
                                ->sortByDesc(function ($comment) use ($user) {
                                    return [$comment->user_id === $user->id ? 1 : 0, $comment->created_at];
                                })
                                ->values()
                                ->map(function ($comment) {
                                    return [
                                        'id' => $comment->id,
                                        'content' => $comment->content,
                                        'created_at' => $comment->created_at,
                                        'user_profile' => optional($comment->user->profile)->only(['first_name', 'last_name', 'profile_picture', 'user_id']),
                                    ];
                                }),
                    'likes' => $achievement->likes->map(function ($like) {
                        return [
                            'user_profile' => optional($like->user->profile)->only(['first_name', 'last_name', 'profile_picture', 'user_id']),
                        ];
                    }),
                ];
            });
            
            return $this->respondWithSuccess([
                'achievements' => $achievementsData,
                'pagination' => [
                    'current_page' => $achievements->currentPage(),
                    'per_page' => $achievements->perPage(),
                    'total' => $achievements->total(),
                    'last_page' => $achievements->lastPage(),
                    'has_more_pages' => $achievements->hasMorePages(),
                    'next_page_url' => $achievements->nextPageUrl(),
                    'prev_page_url' => $achievements->previousPageUrl()
                ]
            ]);
        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage(), 500);
        }
    }
    public function userAchievements(){
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        try{
            $achievements = Achievement::with([
                            'user.profile:id,user_id,first_name,last_name,profile_picture',
                            'comments.user.profile:id,user_id,first_name,last_name,profile_picture',
                            'likes.user.profile:id,user_id,first_name,last_name,profile_picture',
                        ])
                        ->where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function ($achievement)use($user) {
                            $likedByUser = $achievement->likes->contains('user_id', $user->id);
                            
                            return [
                                'id' => $achievement->id,
                                'user_id' => $achievement->user_id,
                                'type' => $achievement->type,
                                'title' => $achievement->title,
                                'description' => $achievement->description,
                                'like_count' => $achievement->like_count,
                                'is_liked' => $likedByUser,
                                'comment_count' => $achievement->comment_count,
                                'created_at' => $achievement->created_at,
                                'user_profile' => optional($achievement->user->profile)->only(['first_name', 'last_name', 'profile_picture']),
                                'comments' => $achievement->comments
                                ->sortByDesc(function ($comment) use ($user) {
                                    return [$comment->user_id === $user->id ? 1 : 0, $comment->created_at];
                                })
                                ->values()
                                ->map(function ($comment) {
                                    return [
                                        'content' => $comment->content,
                                        'created_at' => $comment->created_at,
                                        'user_profile' => optional($comment->user->profile)->only(['first_name', 'last_name', 'profile_picture', 'user_id']),
                                    ];
                                }),
                                'likes' => $achievement->likes->map(function ($like) {
                                    return [
                                        'user_profile' => optional($like->user->profile)->only(['first_name', 'last_name', 'profile_picture', 'user_id']),
                                    ];
                                }),
                            ];
                        });
            return $this->respondWithSuccess(['achievements' => $achievements]);
        } catch (\Exception $e){
            return $this->respondWithError($e->getMessage(), 500);
        }
    }

    public function userConnectionsAchievements(Request $request){
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        try{
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            
            $userConnections = Connection::where('requester_id', $user->id)->orWhere('addressee_id', $user->id)
                            ->where('status', 'accepted')->select('addressee_id', 'requester_id')
                            ->get()
                            ->map(function($connection)use($user){
                                return $connection->addressee_id == $user->id ? $connection->requester_id : $connection->addressee_id;
                            })->unique()->all();

                            $achievements = Achievement::with([
                                'user.profile:id,user_id,first_name,last_name,profile_picture',
                                'comments.user.profile:id,user_id,first_name,last_name,profile_picture',
                                'likes.user.profile:id,user_id,first_name,last_name,profile_picture',
                            ])
                            ->whereIn( 'achievements.user_id', $userConnections)
                            ->orderBy('created_at', 'desc')
                            ->paginate($perPage, ['*'], 'page', $page);
                            
                            $achievementsData = $achievements->getCollection()->map(function ($achievement)use($user) {
                                $likedByUser = $achievement->likes->contains('user_id', $user->id);

                                return [
                                    'id' => $achievement->id,
                                    'user_id' => $achievement->user_id,
                                    'type' => $achievement->type,
                                    'title' => $achievement->title,
                                    'description' => $achievement->description,
                                    'like_count' => $achievement->like_count,
                                    'is_liked' => $likedByUser,
                                    'comment_count' => $achievement->comment_count,
                                    'created_at' => $achievement->created_at,
                                    'user_profile' => optional($achievement->user->profile)->only(['first_name', 'last_name', 'profile_picture']),
                                    'comments' => $achievement->comments
                                    ->sortByDesc(function ($comment) use ($user) {
                                        return [$comment->user_id === $user->id ? 1 : 0, $comment->created_at];
                                    })
                                    ->values()
                                    ->map(function ($comment) {
                                        return [
                                            'content' => $comment->content,
                                            'created_at' => $comment->created_at,
                                            'user_profile' => optional($comment->user->profile)->only(['first_name', 'last_name', 'profile_picture', 'user_id']),
                                        ];
                                    }),
                                    'likes' => $achievement->likes->map(function ($like) {
                                        return [
                                            'user_profile' => optional($like->user->profile)->only(['first_name', 'last_name', 'profile_picture', 'user_id']),
                                        ];
                                    }),
                                ];
                            });                        
            
                return $this->respondWithSuccess([
                    'achievements' => $achievementsData,
                    'pagination' => [
                        'current_page' => $achievements->currentPage(),
                        'per_page' => $achievements->perPage(),
                        'total' => $achievements->total(),
                        'last_page' => $achievements->lastPage(),
                        'has_more_pages' => $achievements->hasMorePages(),
                        'next_page_url' => $achievements->nextPageUrl(),
                        'prev_page_url' => $achievements->previousPageUrl()
                    ]
                ]);
            } catch (\Exception $e){
                return $this->respondWithError($e->getMessage(), 500);

        }
    }
    public function popularAchievements(Request $request){
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        try{
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);

            $achievements = Achievement::with([
                                'user.profile:id,user_id,first_name,last_name,profile_picture',
                                'comments.user.profile:id,user_id,first_name,last_name,profile_picture',
                                'likes.user.profile:id,user_id,first_name,last_name,profile_picture',
                            ])
                            ->orderBy('like_count', 'desc')
                            ->orderBy('created_at', 'desc') // Secondary sort for consistency
                            ->paginate($perPage, ['*'], 'page', $page);
                            
                            $achievementsData = $achievements->getCollection()->map(function ($achievement)use($user) {
                                $likedByUser = $achievement->likes->contains('user_id', $user->id);
                                
                                return [
                                    'id' => $achievement->id,
                                    'user_id' => $achievement->user_id,
                                    'type' => $achievement->type,
                                    'title' => $achievement->title,
                                    'description' => $achievement->description,
                                    'like_count' => $achievement->like_count,
                                    'is_liked' => $likedByUser,
                                    'comment_count' => $achievement->comment_count,
                                    'created_at' => $achievement->created_at,
                                    'user_profile' => optional($achievement->user->profile)->only(['first_name', 'last_name', 'profile_picture']),
                                    'comments' => $achievement->comments
                                    ->sortByDesc(function ($comment) use ($user) {
                                        return [$comment->user_id === $user->id ? 1 : 0, $comment->created_at];
                                    })
                                    ->values()
                                    ->map(function ($comment) {
                                        return [
                                            'content' => $comment->content,
                                            'created_at' => $comment->created_at,
                                            'user_profile' => optional($comment->user->profile)->only(['first_name', 'last_name', 'profile_picture', 'user_id']),
                                        ];
                                    }),
                                    'likes' => $achievement->likes->map(function ($like) {
                                        return [
                                            'user_profile' => optional($like->user->profile)->only(['first_name', 'last_name', 'profile_picture', 'user_id']),
                                        ];
                                    }),
                                ];
                            });         
        return $this->respondWithSuccess([
            'achievements' => $achievementsData,
            'pagination' => [
                'current_page' => $achievements->currentPage(),
                'per_page' => $achievements->perPage(),
                'total' => $achievements->total(),
                'last_page' => $achievements->lastPage(),
                'has_more_pages' => $achievements->hasMorePages(),
                'next_page_url' => $achievements->nextPageUrl(),
                'prev_page_url' => $achievements->previousPageUrl()
            ]
        ]);
        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $achievement)
    {
        //
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        try{
            $achievement = Achievement::with([
                'user.profile:id,user_id,first_name,last_name,profile_picture',
                'comments.user.profile:id,user_id,first_name,last_name,profile_picture',
                'likes.user.profile:id,user_id,first_name,last_name,profile_picture',
            ])->findOrFail($achievement);
            $likedByUser = $achievement->likes->contains('user_id', $user->id);
            return $this->respondWithSuccess([
                'achievement' => [
                    'id' => $achievement->id,
                    'user_id' => $achievement->user_id,
                    'type' => $achievement->type,
                    'title' => $achievement->title,
                    'description' => $achievement->description,
                    'like_count' => $achievement->like_count,
                    'is_liked' => $likedByUser,
                    'comment_count' => $achievement->comment_count,
                    'created_at' => $achievement->created_at,
                    'user_profile' => optional($achievement->user->profile)->only(['first_name', 'last_name', 'profile_picture']),
                    'comments' => $achievement->comments
                                ->sortByDesc(function ($comment) use ($user) {
                                    return [$comment->user_id === $user->id ? 1 : 0, $comment->created_at];
                                })
                                ->values()
                                ->map(function ($comment) {
                                    return [
                                        'id' => $comment->id,
                                        'content' => $comment->content,
                                        'created_at' => $comment->created_at,
                                        'user_profile' => optional($comment->user->profile)->only(['first_name', 'last_name', 'profile_picture', 'user_id']),
                                    ];
                                }),
                    'likes' => $achievement->likes->map(function ($like) {
                        return [
                            'user_profile' => optional($like->user->profile)->only(['first_name', 'last_name', 'profile_picture', 'user_id']),
                        ];
                    }),
                ]
            ]);
        }catch(\Exception $e){
            return $this->respondWithError($e->getMessage(), 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAchievementRequest $request)
    {
        //
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        
            $achievement = new Achievement();
            $achievement->user_id = $user->id;
            $achievement->title = $request->title;
            $achievement->type = $request->type;
            $achievement->description = $request->description??null;
            $achievement->organization = $request->organization;
            
            // Handle date fields with proper mapping for projects
            if ($request->type == 'project' && $request->filled('start_date')) {
                $achievement->achieved_at = $request->start_date;
            } else {
                $achievement->achieved_at = $request->achieved_at;
            }
            if($achievement->type == 'project'){
                $achievement->end_date = $request->filled('end_date') ? $request->end_date : null;

            }
            $achievement->end_date = $request->filled('end_date') ? $request->end_date : null;
            
            $achievement->certificate_url = $request->certificate_url??null;
            $achievement->project_url = $request->project_url??null;

            // Handle image upload - both file upload and base64
            if($request->hasFile('image_path')){
                $file = $request->file('image_path');
                $path = $file->store('achievements', 'public');
                $achievement->image_path = $path;
            }

            try{
                DB::beginTransaction();
                if($achievement->type == 'award'){
                    $award = new Award();
                    $award->user_id = $user->id;
                    $award->title = $achievement->title;
                    $award->description = $achievement->description;
                    $award->achieved_at = $achievement->achieved_at;
                    $award->image_path = $achievement->image_path;
                    $award->organization = $achievement->organization;
                    $award->certificate_url = $achievement->certificate_url;
                    $award->save();
                }
                elseif($achievement->type == 'certification'){
                    $certificate = new Certificate();
                    $certificate->user_id = $user->id;
                    $certificate->title = $achievement->title;
                    $certificate->description = $achievement->description;
                    $certificate->achieved_at = $achievement->achieved_at;
                    $certificate->image_path = $achievement->image_path;
                    $certificate->organization = $achievement->organization;
                    $certificate->certificate_url = $achievement->certificate_url;
                    $certificate->save();
                }
                elseif($achievement->type == 'project'){
                    $project = new Project();
                    $project->user_id = $user->id;
                    $project->title = $achievement->title;
                    $project->technologies_used = $achievement->organization;
                    $project->description = $achievement->description;
                    $project->start_date = $achievement->achieved_at;
                    $project->end_date = $achievement->end_date;
                    $project->github_url = $achievement->certificate_url;
                    $project->project_url = $achievement->project_url;
                    $project->save();
                    
                    // Only create project image if an image was uploaded
                    $projectImage = null;
                    if ($achievement->image_path) {
                        $projectImage = $project->projectImages()->create([
                            'image_path' => $achievement->image_path,
                            'project_id' => $project->id
                        ]);
                    }
                    
                    // Store project data for response
                    $projectData = [
                        'project' => $project,
                        'project_image' => $projectImage
                    ];
                }
                elseif($achievement->type == 'job'){
                    $job = new WorkExperience();
                    $job->user_id = $user->id;
                    $job->position = $achievement->title;
                    $job->company_name = $achievement->organization;
                    $job->start_date = $achievement->achieved_at;
                    $job->description = $achievement->description;
                    $job->is_current = true;
                    $job->save();
                }
        
                // Save main achievement record
                $achievement->save();
                DB::commit();
                
                // Prepare response data
                $responseData = ['achievement' => $achievement];
                if (isset($projectData)) {
                    $responseData = array_merge($responseData, $projectData);
                }
                
                return $this->respondWithSuccess($responseData);

        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e->getMessage());
            return $this->respondWithError($e->getMessage(), 500);
        }
    }




    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAchievementRequest $request, string $achievement)
    {
        //
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        try{
            DB::beginTransaction();
            $achievement = Achievement::findOrFail($achievement);
            if($achievement->user_id != $user->id){
                return $this->respondWithError('You are not authorized to update this achievement', 403);
            };
            $achievement->title = $request->title ?? $achievement->title;
            $achievement->type = $request->type ?? $achievement->type;
            $achievement->description = $request->description ?? $achievement->description;
            $achievement->organization = $request->organization ?? $achievement->organization;
            $achievement->achieved_at = $request->achieved_at ?? $achievement->achieved_at;
            $achievement->end_date = $request->end_date ?? $achievement->end_date;
            $achievement->certificate_url = $request->certificate_url ?? $achievement->certificate_url;
            $achievement->project_url = $request->project_url ?? $achievement->project_url;
            
            // Handle image upload - both file upload and base64
            if($request->hasFile('image_path')){
                $file = $request->file('image_path');
                $path = $file->store('achievements', 'public');
                $achievement->image_path = $path;
            }
            if($achievement->type == 'award'){
                $award = Award::where('user_id', $user->id)->where('title', $achievement->title)->where('achieved_at', $achievement->achieved_at)
                ->where('organization', $achievement->organization)->first();
                $award->title = $achievement->title;
                $award->description = $achievement->description;
                $award->achieved_at = $achievement->achieved_at;
                $award->image_path = $achievement->image_path;
                $award->organization = $achievement->organization;
                $award->certificate_url = $achievement->certificate_url;
                $award->save();
            }
            elseif($achievement->type == 'certificate'){
                $certificate = Certificate::where('user_id', $user->id)
                ->where('title', $achievement->title)
                ->where('achieved_at', $achievement->achieved_at)
                ->first();
                $certificate->title = $achievement->title;
                $certificate->description = $achievement->description;
                $certificate->achieved_at = $achievement->achieved_at;
                $certificate->image_path = $achievement->image_path;
                $certificate->organization = $achievement->organization;
                $certificate->certificate_url = $achievement->certificate_url;
                $certificate->save();
            }
            elseif($achievement->type == 'project'){
                $project = Project::where('user_id', $user->id)
                ->where('title', $achievement->title)
                ->where('start_date', $achievement->achieved_at)->first();
                $project->title = $achievement->title;
                $project->technologies_used = $achievement->organization;
                $project->description = $achievement->description;
                $project->start_date = $achievement->achieved_at;
                $project->end_date = $achievement->end_date;
                $project->github_url = $achievement->certificate_url;
                $project->project_url = $achievement->project_url;
                $project->save();
                
                // Handle project image update - only if image was provided
                if ($achievement->image_path) {
                    $project_image = $project->projectImages()->where('project_id', $project->id)->first();
                    if ($project_image) {
                        $project_image->image_path = $achievement->image_path;
                        $project_image->save();
                    } else {
                        // Create new project image if none exists
                        $project->projectImages()->create([
                            'image_path' => $achievement->image_path,
                            'project_id' => $project->id
                        ]);
                    }
                }
            }
            elseif($achievement->type == 'job'){
                $job = WorkExperience::where('user_id', $user->id)
                ->where('position', $achievement->title)
                ->where('start_date', $achievement->achieved_at)->first();
                $job->position = $achievement->title;
                $job->company_name = $achievement->organization;
                $job->start_date = $achievement->achieved_at;
                $job->description = $achievement->description;
                $job->save();
            }
            $achievement->save();
            DB::commit();
            return $this->respondWithSuccess(['achievement' => $achievement]);
        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e->getMessage());
            return $this->respondWithError($e->getMessage(), 500);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $achievement)
    {
        //
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        try{
            DB::beginTransaction();
            $achievement = Achievement::find($achievement);
            if(!$achievement){
                return $this->respondWithError('Achievement not found', 404);
            }
            if($achievement->user_id != $user->id && !$user->hasRole('admin')){
                return $this->respondWithError('You are not authorized to delete this achievement', 403);
            };
            if($achievement->type == 'award'){
                $award = Award::where('user_id', $user->id)->where('title', $achievement->title)
                ->where('achieved_at', $achievement->achieved_at)->where('organization', $achievement->organization)
                ->first();
                if($award){
                    if($award->user_id != $user->id){
                        DB::rollBack();
                        return $this->respondWithError('You are not authorized to delete this achievement', 403);
                    }
                    $award->delete();
                }
                }
               
            elseif($achievement->type == 'certificate'){
                $certificate = Certificate::where('user_id', $user->id)
                ->where('title', $achievement->title)
                ->where('achieved_at', $achievement->achieved_at)
                ->first();
                if(!$certificate){
                    if($certificate->user_id != $user->id){
                        DB::rollBack();
                        return $this->respondWithError('You are not authorized to delete this certificate', 403);
                    }
                    $certificate->delete();
                }
                }
                
            elseif($achievement->type == 'project'){
                $project = Project::where('user_id', $user->id)
                ->where('title', $achievement->title)
                ->where('start_date', $achievement->achieved_at)->first();
                if($project){
                    if($project->user_id != $user->id){
                        DB::rollBack();
                        return $this->respondWithError('You are not authorized to delete this project', 403);
                    }
                    
                    // Delete project image if it exists
                    $project_image = $project->projectImages()->where('project_id', $project->id)->first();
                    if ($project_image) {
                        $project_image->delete();
                    }
                    
                    $project->delete(); 
                }
               
            }
            elseif($achievement->type == 'job'){
                $job = WorkExperience::where('user_id', $user->id)
                ->where('position', $achievement->title)
                ->where('start_date', $achievement->achieved_at)->first();
                if($job){
                    if($job->user_id != $user->id){
                        DB::rollBack();
                        return $this->respondWithError('You are not authorized to delete this job', 403);
                    }
                    $job->delete();
                }
               
            }

            $achievement->delete();
            DB::commit();
            return $this->respondWithSuccess(['achievement' => $achievement],"Achievement deleted successfully", 200);
        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e->getMessage());
            return $this->respondWithError($e->getMessage(), 500);
        }
    }

}