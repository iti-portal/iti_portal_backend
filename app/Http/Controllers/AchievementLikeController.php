<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AchievementLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchievementLikeController extends Controller
{
    //
    public function like(Request $request){
        //
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        $request->validate([
            $request->achievement_id => 'required',
        ]);
        try{
            $achievement = Achievement::findOrFail($request->achievement_id);
            $like = AchievementLike::where('achievement_id', $request->achievement_id)
            ->where('user_id', $user->id)->get();
            if($like){
                return $this->respondWithError('You have already liked this achievement', 400);
            };
            DB::beginTransaction();
            $like = new AchievementLike();
            $like->user_id = $user->id;
            $like->achievement_id = $request->achievement_id;
            $like->save();
            $achievement->like_count = $achievement->like_count + 1;
            $achievement->save();
            DB::commit();
            return $this->respondWithSuccess('Like added successfully', $like);
        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e);
            return $this->respondWithError($e->getMessage(), 500);
    }
}   
    public function unlike(Request $request){
        //
        $user = auth()->user();
        if(!$user){
            return $this->respondWithError('User not found', 404);
        }
        $request->validate([
            $request->achievement_id => 'required',
        ]);
        try{
            $achievement = Achievement::findOrFail($request->achievement_id);
            $like = AchievementLike::where('achievement_id', $request->achievement_id)
            ->where('user_id', $user->id)->get();
            if(!$like){
                return $this->respondWithError('You have not liked this achievement', 400);
            };
            DB::beginTransaction();
            $like->delete();
            $achievement->like_count = $achievement->like_count - 1;
            $achievement->save();
            DB::commit();
            return $this->respondWithSuccess('Like deleted successfully');
        }catch(\Exception $e){
            DB::rollBack();
            \Log::error($e);
            return $this->respondWithError($e->getMessage(), 500);
    }
}

}
