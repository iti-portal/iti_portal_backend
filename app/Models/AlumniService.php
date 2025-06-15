<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlumniService extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'alumni_id',
        'service_type',
        'title',
        'description',
        'feedback',
        'has_taught_or_presented',
        'evaluation',
    ];
    protected $casts = [
        'has_taught_or_presented' => 'boolean'    
    ];
    public function alumni()
    {
        return $this->belongsTo(User::class, 'alumni_id');
    }
}
