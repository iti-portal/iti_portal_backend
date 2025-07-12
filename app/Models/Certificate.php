<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'organization',
        'achieved_at',
        'certificate_url',
        'image_path',
    ];
    protected $casts = [
        'achieved_at' => 'date',
    ];
}
