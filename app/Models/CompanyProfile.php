<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'description',
        'location',
        'established_at',
        'website',
        'industry',
        'company_size',
        'logo',
    ];

    protected $casts = [
        'established_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
