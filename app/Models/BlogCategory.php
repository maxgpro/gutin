<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class BlogCategory extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = ['name', 'slug', 'description'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'name' => 'array',
        'slug' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            $category->generateSlugsForAllLocales();
        });

        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->generateSlugsForAllLocales();
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
            $name = $this->getTranslation('name', $locale);
            if ($name) {
                $slugs[$locale] = $this->generateUniqueSlug($name, $locale);
            }
        }

        $this->slug = $slugs;
    }

    /**
     * Generate unique slug for specific locale
     */
    private function generateUniqueSlug(string $name, string $locale): string
    {
        $baseSlug = Str::slug($name);
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
        
        // Use PostgreSQL JSON operators
        if (config('database.default') === 'pgsql') {
            return $query->whereRaw("slug->>? = ?", [$locale, $slug])->exists();
        }
        
        // Fallback for SQLite
        return $query->whereRaw("json_extract(slug, '$.{$locale}') = ?", [$slug])->exists();
    }

    /**
     * Scope for ordering by localized name
     */
    public function scopeOrderByLocalizedName($query, ?string $locale = null, string $direction = 'asc')
    {
        $locale = $locale ?: app()->getLocale();
        
        // Use PostgreSQL JSON operators
        if (config('database.default') === 'pgsql') {
            return $query->orderByRaw("name->>? {$direction}", [$locale]);
        }
        
        // Fallback for SQLite
        return $query->orderByRaw("json_extract(name, '$.{$locale}') {$direction}");
    }

    /**
     * Find category by slug in current locale
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?: app()->getLocale();
        
        // Use PostgreSQL JSON operators
        if (config('database.default') === 'pgsql') {
            return static::whereRaw("slug->>? = ?", [$locale, $slug])->first();
        }
        
        // Fallback for SQLite
        return static::whereRaw("json_extract(slug, '$.{$locale}') = ?", [$slug])->first();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function publishedPosts(): HasMany
    {
        return $this->posts()->where('status', BlogPost::STATUS_PUBLISHED)->whereNotNull('published_at');
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
}
