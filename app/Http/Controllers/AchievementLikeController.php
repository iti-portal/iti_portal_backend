<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AchievementLike;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchievementLikeController extends Controller
{
    //
    protected $firebase;

    public function __construct(FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    }
    public function toggleLike(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->respondWithError('User not found', 404);
        }
        
        $request->validate([
            'achievement_id' => 'required',
        ]);
        $is_liked = null;
        try {
            $achievement = Achievement::find($request->achievement_id);
            if (!$achievement) {
                return $this->respondWithError('Achievement not found', 404);
            }
            $existingLike = AchievementLike::where('achievement_id', $request->achievement_id)
                ->where('user_id', $user->id)
                ->first();
            
            DB::beginTransaction();
            
            if ($existingLike) {
                $existingLike->delete();
                $achievement->decrement('like_count');
                $action = 'unliked';
                $is_liked = false;
            } else {
                AchievementLike::create([
                    'user_id' => $user->id,
                    'achievement_id' => $request->achievement_id,
                ]);
                $achievement->increment('like_count');
                $action = 'liked';
                $is_liked = true;

                // Send notification to the achievement owner
                $this->firebase->send(
                    $achievement->user_id,
                    [
                        'title' => 'Achievement Likes',
                        'body' => "{$user->profile->full_name} liked your achievement {$achievement->title}.",
                        'sender_id' => $user->id,
                        'type' => 'achievement_like',
                        'target_id' => $achievement->id
                    ]
                    );
                
            }
            
            DB::commit();
            
            return $this->respondWithSuccess([
                'action' => $action,
                'like_count' => $achievement->fresh()->like_count,
                'is_liked' => $is_liked
            ], "Achievement {$action} successfully");
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Toggle like error: ' . $e->getMessage());
            return $this->respondWithError($e->getMessage(), 500);

        }
    }
}