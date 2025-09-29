<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use App\Models\Traits\OrdersByLocalizedTitle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Translatable\HasTranslations;

class BlogCategory extends Model
{
    use HasFactory, HasTranslations, HasSlug, OrdersByLocalizedTitle;

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

    // Ordering scope moved to OrdersByLocalizedTitle trait

    /**
     * Scope: only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
