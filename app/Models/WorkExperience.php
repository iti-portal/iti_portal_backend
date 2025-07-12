<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'user_id',
        'company_name',
        'start_date',
        'end_date',
        'description',
        'is_current',
        'position',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
