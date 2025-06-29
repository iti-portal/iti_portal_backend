<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AchievementComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchievementCommentController extends Controller
{
    //
    public function Store(Request $request){
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        $request->validate([
            'achievement_id' => 'required|exists:achievements,id',
            'content' => 'required|string|max:1000',
        ]);
        try{
            $achievement = Achievement::findOrFail($request->achievement_id);
            DB::beginTransaction();
            $comment = new AchievementComment();
            $comment->user_id = $user->id;
            $comment->achievement_id = $request->achievement_id;
            $comment->content = $request->content;
            $comment->save();
            $achievement->increment('comment_count');
            $achievement->save();
            DB::commit();
            return $this->respondWithSuccess('Comment added successfully', $comment);
        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e);
            return $this->respondWithError($e->getMessage(), 500);

        }

    }
    public function delete(Request $request, AchievementComment $comment){
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        if(!$comment){
            return $this->respondWithError('Comment not found', 404);
        }
        try{
            DB::beginTransaction();
            $achievement = Achievement::findOrFail($comment->achievement_id);
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
