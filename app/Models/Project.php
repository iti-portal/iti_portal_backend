<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
    use HasFactory;
    protected $fillable=[
        'title',
        'technologies_used',
        'description',
        'project_url',
        'github_url',
        'start_date',
        'end_date',
        'is_featured',
        'user_id',
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_featured' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function projectImages()
    {
        return $this->hasMany(ProjectImage::class);
    }
}
