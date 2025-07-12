<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'organization',
        'achieved_at',
        'image_path',
        'certificate_url',
        'project_url',
        'like_count',
        'comment_count'
    ];
    protected $casts = [
        'achieved_at' => 'date'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function likes()
    {
        return $this->hasMany(AchievementLike::class);
    }
    public function comments()
    {
        return $this->hasMany(AchievementComment::class);
    }
}

