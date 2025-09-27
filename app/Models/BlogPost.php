<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class BlogPost extends Model
{
    use HasFactory, HasTranslations;

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            $post->generateSlugsForAllLocales();
        });

        static::updating(function ($post) {
            if ($post->isDirty('title')) {
                $post->generateSlugsForAllLocales();
            }
        });
    }

    /**
     * Generate slugs for all available locales
     */
    public function generateSlugsForAllLocales(): void
    {
        $availableLocales = array_keys(config('app.available_locales'));
        $slugs = [];

        foreach ($availableLocales as $locale) {
            $title = $this->getTranslation('title', $locale);
            if ($title) {
                $slugs[$locale] = $this->generateUniqueSlug($title, $locale);
            }
        }

        $this->slug = $slugs;
    }

    /**
     * Generate unique slug for specific locale
     */
    private function generateUniqueSlug(string $title, string $locale): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->slugExistsInLocale($slug, $locale)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists in specific locale
     */
    private function slugExistsInLocale(string $slug, string $locale): bool
    {
        $query = static::query();
        
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }
        
        // PostgreSQL JSONB operator to extract text value for the given locale
        return $query->whereRaw("slug->>? = ?", [$locale, $slug])->exists();
    }

    /**
     * Scope for ordering by localized title
     */
    public function scopeOrderByLocalizedTitle($query, ?string $locale = null, string $direction = 'asc')
    {
        $locale = $locale ?: app()->getLocale();
        
        // PostgreSQL JSONB operator to order by localized title
        return $query->orderByRaw("title->>? {$direction}", [$locale]);
    }

    /**
     * Find post by slug in current locale
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?: app()->getLocale();
        
        // PostgreSQL JSONB operator to match by localized slug
        return static::whereRaw("slug->>? = ?", [$locale, $slug])->first();
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

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Resolve route model binding for slug
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If it's numeric, treat as ID
        if (is_numeric($value)) {
            return $this->where('id', $value)->first();
        }

        // Otherwise treat as slug and search in current locale
        return $this->findBySlug($value);
    }

    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200)); // Assume 200 words per minute
    }
}
