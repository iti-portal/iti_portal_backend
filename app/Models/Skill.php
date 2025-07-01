<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'name',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_skills');
    }
    
    public function job_skills()
    {
        return $this->hasMany(JobSkill::class, 'skill_id');
    }
    public function user_skills()
    {
        return $this->hasMany(UserSkill::class, 'skill_id');
    }
}
