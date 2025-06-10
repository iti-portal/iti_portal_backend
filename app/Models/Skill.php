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
        'description',
        'category_id',
    ];
    
    public function job_skills()
    {
        return $this->hasMany(JobSkill::class, 'skill_id');
    }
    public function user_skills()
    {
        return $this->hasMany(UserSkill::class, 'skill_id');
    }
}
