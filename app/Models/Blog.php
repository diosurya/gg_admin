<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'blogs';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'published_at'    => 'datetime',
        'publish_at'      => 'datetime',
        'view_count'     => 'integer',
        'likes_count'     => 'integer',
        'comments_count'  => 'integer',
        'reading_time'    => 'integer',
        'is_featured'     => 'boolean',
        'allow_comments'  => 'boolean',
    ];

    const STATUS_DRAFT     = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_ARCHIVED  = 'archived';

    public static function getStatuses()
    {
        return [
            self::STATUS_DRAFT     => 'Draft',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_ARCHIVED  => 'Archived',
        ];
    }


    public function categories()
    {
        return $this->belongsToMany(BlogCategory::class, 'blog_category_relationships', 'blog_id', 'category_id')
                    ->using(BlogCategoryRelationship::class)
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    // public function tags()
    // {
    //     return $this->belongsToMany(BlogTag::class, 'blog_tags', 'blog_id', 'tag_id')
    //                 ->using(BlogTagPivot::class)
    //                 ->withTimestamps()
    //                  ->withPivot('id');
    // }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** -------------------
     * 🔧 Mutators & Accessors
     * ------------------- */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    // public function getExcerptAttribute($value)
    // {
    //     return !empty($value) ? $value : Str::limit(strip_tags($this->content), 150);
    // }

    /** -------------------
     * 🔎 Scopes
     * ------------------- */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                     ->where('published_at', '<=', now());
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('slug', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    public function scopeByStatus($query, $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeByCategories($query, $categoryIds)
    {
        return !empty($categoryIds)
            ? $query->whereHas('categories', fn($q) => $q->whereIn('blog_category_relationships.category_id', $categoryIds))
            : $query;
    }

    // public function scopeByTags($query, $tagIds)
    // {
    //     return !empty($tagIds)
    //         ? $query->whereHas('tags', fn($q) => $q->whereIn('blog_tags.id', $tagIds))
    //         : $query;
    // }

    /** -------------------
     * 🚀 Boot
     * ------------------- */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            if (empty($blog->id)) {
                $blog->id = (string) Str::uuid();
            }
            if (auth()->check()) {
                $blog->created_by = auth()->id();
            }
        });

        static::updating(function ($blog) {
            if (auth()->check()) {
                $blog->updated_by = auth()->id();
            }
        });
    }
}
