<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class BlogPost extends Model
{
    use HasFactory, HasTranslations, HasSlug;

    public $translatable = ['title', 'slug', 'excerpt', 'content'];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
        self::STATUS_ARCHIVED,
    ];

    protected $fillable = [
        'user_id',
        'blog_category_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'meta_data',
        'status',
        'published_at',
        'views_count',
    ];

    protected $casts = [
        'title' => 'array',
        'slug' => 'array',
        'excerpt' => 'array',
        'content' => 'array',
        'meta_data' => 'array',
        'published_at' => 'datetime',
        'views_count' => 'integer',
    ];

    /**
     * Scope for ordering by localized title
     */
    public function scopeOrderByLocalizedTitle($query, ?string $locale = null, string $direction = 'asc')
    {
        $locale = $locale ?: app()->getLocale();
        
        // PostgreSQL JSONB operator to order by localized title
        return $query->orderByRaw("title->>? {$direction}", [$locale]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    public function publish(): void
    {
        $this->update([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => $this->published_at ?? now(),
        ]);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }
}
