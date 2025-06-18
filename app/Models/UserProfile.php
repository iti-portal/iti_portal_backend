<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'username',
        'summary',
        'phone',
        'whatsapp',
        'linkedin',
        'github',
        'portfolio_url',
        'profile_picture',
        'cover_photo',
        'governorate',
        'available_for_freelance',
        'track',
        'intake',
        'student_status',
        'nid_front_image',
        'nid_back_image',
    ];

    protected $casts = [
        'available_for_freelance' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
    
    public function skills()
    {
        return $this->hasMany(UserSkill::class, 'user_id')
            ->with(['skill' => function($q) {
                $q->select('id', 'name', 'description');
            }]);
    }
}
