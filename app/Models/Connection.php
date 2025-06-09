<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'addressee_id',
        'requester_id',
        'status',
        'message',
    ];
    protected $casts = [
        'requested_at' => 'datetime',
    ];
    public function addressee()
    {
        return $this->belongsTo(User::class, 'addressee_id');
    }
    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
