<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BlogTag extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tags';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [
        'id',
    ];

    // Relationships
    public function blogs()
    {
        return $this->belongsToMany(Blog::class, 'blog_tags', 'tag_id', 'blog_id')
                    ->withTimestamps();
    }

    // Scopes
    public function scopePopular($query, $limit = 10)
    {
        return $query->withCount('blogs')
                     ->orderBy('blogs_count', 'desc')
                     ->limit($limit);
    }

    // Accessors
    public function getBlogsCountAttribute()
    {
        return $this->blogs()->count();
    }

    public function getColorAttribute($value)
    {
        return $value ?: '#6c757d';
    }
}
