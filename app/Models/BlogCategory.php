<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class BlogCategory extends Model
{
    use HasFactory, HasTranslations, HasSlug;

    public $translatable = ['title', 'slug', 'description'];

    protected $fillable = [
        'title',
        'slug',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'title' => 'array',
        'slug' => 'array',
        'description' => 'array',
        'is_active' => 'boolean',
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

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function publishedPosts(): HasMany
    {
        return $this->posts()->where('status', BlogPost::STATUS_PUBLISHED)->whereNotNull('published_at');
    }
}
