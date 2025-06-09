<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'achieved_at',
        'image_path',
        'organization',
        'user_id',
        'certificate_url'
    ];
    protected $casts = [
        'achieved_at' => 'date',
    ];
}
