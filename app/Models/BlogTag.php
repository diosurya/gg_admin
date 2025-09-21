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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->id)) {
                $tag->id = (string) Str::uuid();
            }
            if (auth()->check()) {
                $tag->created_by = auth()->id();
            }
        });

        static::updating(function ($tag) {
            if (auth()->check()) {
                $tag->updated_by = auth()->id();
            }
        });
    }
}
