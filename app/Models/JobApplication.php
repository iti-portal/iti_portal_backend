<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'job_id',
        'status',
        'cover_letter',
        'company_notes',
        'applied_at',
        'cv_path',
        'cv_downloaded_at',
        'profile_viewed_at',
        'is_reviewed',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'cv_downloaded_at' => 'datetime',
        'profile_viewed_at' => 'datetime',
        'is_reviewed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job()
    {
        return $this->belongsTo(AvailableJob::class, 'job_id');
    }
}
