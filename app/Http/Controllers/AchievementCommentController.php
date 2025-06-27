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
            'achievement_id' => 'required',
            'comment' => 'required',
        ]);
        try{
            $achievement = Achievement::findOrFail($request->achievement_id);
            DB::beginTransaction();
            $comment = new AchievementComment();
            $comment->user_id = $user->id;
            $comment->achievement_id = $request->achievement_id;
            $comment->comment = $request->comment;
            $comment->save();
            $achievement->comment_count = $achievement->comment_count + 1;
            $achievement->save();
            DB::commit();
            return $this->respondWithSuccess('Comment added successfully', $comment);
        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e);
            return $this->respondWithError($e->getMessage(), 500);

        }

    }
    public function delete(Request $request){
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        try{
            DB::beginTransaction();
            $comment = AchievementComment::findOrFail($request->comment);
            $achievement = Achievement::findOrFail($comment->achievement_id);
            if($comment->user_id != $user->id && $achievement->user_id != $user->id){
                return $this->respondWithError('You are not authorized to delete this comment', 403);
            }
            $achievement->comment_count = $achievement->comment_count - 1;
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
