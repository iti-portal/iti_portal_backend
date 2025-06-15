<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;



class User extends Authenticatable implements JWTSubject , MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'email_verified_at',
        'status',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        $profile = $this->profile;
        $roles = $this->roles->pluck('name')->toArray();

        return [
            'user_id' => $this->id,
            'email' => $this->email,
            'roles' => $roles,
            'status' => $this->status,
            'name' => $profile ? $profile->full_name : null,
            'avatar' => $profile ? $profile->profile_picture : null,
            'permissions' => $this->getAllPermissions()->pluck('name')->toArray(),
        ];
    }

    // Relationships

    public function alumniServices()
    {
        return $this->hasMany(AlumniService::class, 'alumni_id');
    }
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class);
    }

    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function workExperiences()
    {
        return $this->hasMany(WorkExperience::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'user_skills')
                    ->withTimestamps();
    }
    public function awards()
    {
        return $this->hasMany(Award::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                     ->whereHas('roles', function ($roleQuery) {
                         $roleQuery->whereIn('name', ['student', 'alumni', 'company']);
                     });
    }

    public function scopeByRole($query, $role)
    {
        return $query->role($role);
    }

    // Helper Methods
    public function isVerified()
    {
        return $this->email_verified_at !== NULL;
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isCompany()
    {
        return $this->hasRole('company');
    }

    public function isStudentOrAlumni()
    {
        return $this->hasRole(['student', 'alumni']);
    }

    public function isStaff()
    {
        return $this->hasRole('staff');
    }

    public function getFullNameAttribute()
    {
        if ($this->isCompany() && $this->companyProfile) {
            return $this->companyProfile->company_name;
        }

        return $this->profile ? $this->profile->full_name : $this->email;
    }


}
