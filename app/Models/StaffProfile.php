<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'full_name',
        'position',
        'department',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
