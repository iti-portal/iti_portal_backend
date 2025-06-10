<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AchievementLike extends Model
{
    //
    use HasFactory;
    protected $fillable = ['user_id', 'achievement_id'];

    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
