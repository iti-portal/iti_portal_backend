<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AchievementLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchievementLikeController extends Controller
{
    //
    public function toggleLike(Request $request)
{
    $user = auth()->user();
    if (!$user) {
        return $this->respondWithError('User not found', 404);
    }
    
    $request->validate([
        'achievement_id' => 'required|exists:achievements,id',
    ]);
    
    try {
        $achievement = Achievement::findOrFail($request->achievement_id);
        
        $existingLike = AchievementLike::where('achievement_id', $request->achievement_id)
            ->where('user_id', $user->id)
            ->first();
        
        DB::beginTransaction();
        
        if ($existingLike) {
            $existingLike->delete();
            $achievement->decrement('like_count');
            $action = 'unliked';
        } else {
            AchievementLike::create([
                'user_id' => $user->id,
                'achievement_id' => $request->achievement_id,
            ]);
            $achievement->increment('like_count');
            $action = 'liked';
        }
        
        DB::commit();
        
        return $this->respondWithSuccess([
            'action' => $action,
            'like_count' => $achievement->fresh()->like_count
        ], "Achievement {$action} successfully");
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Toggle like error: ' . $e->getMessage());
        return $this->respondWithError($e->getMessage(), 500);

}}
}