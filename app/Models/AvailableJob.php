<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\HasDatabaseNotifications;

class AvailableJob extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'company_id',
        'title',
        'description',
        'requirements',
        'job_type',
        'experience_level',
        'salary_min',
        'salary_max',
        'application_deadline',
        'status',
        'is_featured',
        'is_remote',
        'applications_count',
       'review_applications',
       'interview_applications',
       'hired_applications',
       'rejected_applications'

    ];
    public function company()
    {
        return $this->belongsTo(User::class, 'company_id');
    }
    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }
    public function job_skills()
    {
        return $this->hasMany(JobSkill::class, 'job_id');
    }
}
