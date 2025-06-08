<?php

namespace App\Models;

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
                    ->withPivot('proficiency_level')
                    ->withTimestamps();
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                     ->where(function ($q) {
                         // For companies: must have company profile
                         $q->whereHas('roles', function ($roleQuery) {
                             $roleQuery->where('name', 'company');
                         })->whereHas('companyProfile')
                         ->orWhere(function ($userQuery) {
                             // For non-companies: must have profile and NID images
                             $userQuery->whereDoesntHave('roles', function ($roleQuery) {
                                 $roleQuery->where('name', 'company');
                             })
                             ->whereHas('profile', function ($profileQuery) {
                                 $profileQuery->whereNotNull('nid_front_image')
                                             ->whereNotNull('nid_back_image');
                             });
                         });
                     })
                     ->whereNotNull('email_verified_at'); // Ensure email is verified
    }

    public function scopeByRole($query, $role)
    {
        return $query->role($role);
    }

    // Helper Methods
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

    public function getRegistrationStep()
    {
        if (!$this->hasVerifiedEmail()) {
            return 'email_verification';
        }

        if ($this->isCompany()) {
            if (!$this->companyProfile) {
                return 'company_profile';
            }
        } else {
            if (!$this->profile) {
                return 'user_profile';
            }

            if (!$this->profile->nid_front_image || !$this->profile->nid_back_image) {
                return 'nid_upload';
            }
        }

        if ($this->status === 'pending') {
            return 'pending_approval';
        }

        return 'completed';
    }

    public function getFullNameAttribute()
    {
        if ($this->isCompany() && $this->companyProfile) {
            return $this->companyProfile->company_name;
        }

        return $this->profile ? $this->profile->full_name : $this->email;
    }
}
