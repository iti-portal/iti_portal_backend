<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AchievementComment;
use App\Services\FirebaseNotificationService;
use Dom\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchievementCommentController extends Controller
{
    //
    protected $firebase;

    public function __construct(FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    }
   
    public function store(Request $request){
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        $request->validate([
            'achievement_id' => 'required|exists:achievements,id',
            'content' => 'required|string|max:1000|min:1',
        ]);
        try{
            $achievement = Achievement::find($request->achievement_id);
            if(!$achievement){
                return $this->respondWithError('Achievement not found', 404);
            }
            DB::beginTransaction();
            $comment = new AchievementComment();
            $comment->user_id = $user->id;
            $comment->achievement_id = $request->achievement_id;
            $comment->content = $request->content;
            $comment->save();
            
            // Load the user profile relationship
            $comment->load('user.profile:id,user_id,first_name,last_name,profile_picture');
            
            $achievement->increment('comment_count');
            $achievement->save();
            DB::commit();

            // Send notification to the achievement owner
            $this->firebase->send(
                $achievement->user_id,[
                    'title' => 'Achievement Comments',
                    'body' => $user->name . ' commented on your achievement: ' . $achievement->title,
                    'sender_id' => $user->id,
                    'type' => 'achievement_comment',
                ]);
            // Format the response with all necessary fields
            return $this->respondWithSuccess([
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at,
                    'user_profile' => optional($comment->user->profile)->only(['first_name', 'last_name', 'profile_picture', 'user_id'])
                ]
            ], 'Comment added successfully');
        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e);
            return $this->respondWithError($e->getMessage(), 500);

        }

    }
    public function destroy(Request $request, $comment){
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        $comment = AchievementComment::find($comment);
        if(!$comment){
            return $this->respondWithError('Comment not found', 404);
        }

        
        try{
            DB::beginTransaction();
            $achievement = Achievement::find($comment->achievement_id);
            if(!$achievement){
                return $this->respondWithError('Achievement not found', 404);
            }
            if($comment->user_id != $user->id && $achievement->user_id != $user->id){
                return $this->respondWithError('You are not authorized to delete this comment', 403);
            }
            $achievement->decrement('comment_count');
            $achievement->save();
            $comment->delete();
            DB::commit();
            return $this->respondWithSuccess('Comment deleted successfully');
        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e);
            return $this->respondWithError($e->getMessage(), 500);

        }
    }
}
