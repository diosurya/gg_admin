<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Page extends Model
{
    protected $table = 'pages';
    
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'template',
        'status',
        'sort_order',
        'is_homepage',
        'show_in_menu',
        'parent_id',
        'view_count',
        
        // SEO Fields
        'seo_title',
        'seo_description',
        'seo_keywords',
        'seo_og_title',
        'seo_og_description',
        'seo_og_image',
        'seo_og_type',
        'seo_twitter_card',
        'seo_twitter_title',
        'seo_twitter_description',
        'seo_twitter_image',
        'seo_canonical_url',
        'seo_robots',
        'seo_schema_markup',
        
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'id' => 'string',
        'parent_id' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
        'is_homepage' => 'boolean',
        'show_in_menu' => 'boolean',
        'view_count' => 'integer',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
        'seo_schema_markup' => 'array',
    ];

    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
    ];

    // Boot method to auto-generate UUID
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            
            // Auto-generate slug if not provided
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
            
            // Set created_by if authenticated user exists
            if (auth()->check()) {
                $model->created_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            // Set updated_by if authenticated user exists
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });
    }

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Page::class, 'parent_id')->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where(function ($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '<=', now());
                    });
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeHomepage($query)
    {
        return $query->where('is_homepage', true);
    }

    public function scopeInMenu($query)
    {
        return $query->where('show_in_menu', true);
    }

    public function scopeParentPages($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    // Accessors & Mutators
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => 'bg-secondary',
            'published' => 'bg-success',
            'archived' => 'bg-dark',
        ];

        return $badges[$this->status] ?? 'bg-secondary';
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('d M Y') : '-';
    }

    public function getFormattedPublishedAtAttribute()
    {
        return $this->published_at ? $this->published_at->format('d M Y H:i') : '-';
    }

    public function getCreatorNameAttribute()
    {
        if ($this->creator) {
            return trim($this->creator->first_name . ' ' . $this->creator->last_name);
        }
        return 'Unknown';
    }

    public function getUpdaterNameAttribute()
    {
        if ($this->updater) {
            return trim($this->updater->first_name . ' ' . $this->updater->last_name);
        }
        return 'Unknown';
    }

    public function getExcerptOrContentAttribute()
    {
        return $this->excerpt ?: Str::limit(strip_tags($this->content), 150);
    }

    public function getFullUrlAttribute()
    {
        return url($this->slug);
    }

    // SEO Methods
    public function getSeoTitleAttribute($value)
    {
        return $value ?: $this->title;
    }

    public function getSeoDescriptionAttribute($value)
    {
        return $value ?: $this->excerpt_or_content;
    }

    public function getSeoOgTitleAttribute($value)
    {
        return $value ?: $this->seo_title;
    }

    public function getSeoOgDescriptionAttribute($value)
    {
        return $value ?: $this->seo_description;
    }

    public function getSeoTwitterTitleAttribute($value)
    {
        return $value ?: $this->seo_title;
    }

    public function getSeoTwitterDescriptionAttribute($value)
    {
        return $value ?: $this->seo_description;
    }

    // Helper Methods
    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               ($this->published_at === null || $this->published_at->isPast());
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?: now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function generateUniqueSlug($title): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? '')->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    // Tree structure helpers
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
    }

    public function getBreadcrumbsAttribute(): array
    {
        $breadcrumbs = [];
        $page = $this;
        
        while ($page) {
            array_unshift($breadcrumbs, [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
            ]);
            $page = $page->parent;
        }
        
        return $breadcrumbs;
    }
}