<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //
    use HasFactory;
    protected $fillable = [
        'author_id',
        'title',
        'content',
        'featured_image',
        'external_link',
        'status',
        'like_count',
        'published_at',
    ];
    protected $casts = [
        'published_at' => 'datetime',
    ];
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    public function likes()
    {
        return $this->hasMany(ArticleLike::class, 'article_id');
    }
}
