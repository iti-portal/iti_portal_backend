<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSkill extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'job_id',
        'skill_id',
        'is_required',
    ];
    protected $casts = [
        'is_required' => 'boolean',
    ];
    public function job()
    {
        return $this->belongsTo(AvailableJob::class, 'job_id');
    }
    public function skill()
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }
}
