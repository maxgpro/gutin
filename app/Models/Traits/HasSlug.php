<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the HasSlug trait
     */
    protected static function bootHasSlug()
    {
        static::creating(function ($model) {
            $model->generateSlugsForAllLocales();
        });

        static::created(function ($model) {
            // Update slugs with ID after creation
            $model->updateSlugsWithId();
        });

        static::updating(function ($model) {
            if ($model->isDirty('title')) {
                $model->generateSlugsForAllLocales();
            }
        });

        static::updated(function ($model) {
            if ($model->wasChanged('title')) {
                $model->updateSlugsWithId();
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
                $slugs[$locale] = $this->generateLocalizedSlug($title, $locale);
            }
        }

        $this->slug = $slugs;
    }

    /**
     * Generate localized slug in format "id-localized-slug"
     * Models can override this method to customize slug generation
     */
    protected function generateLocalizedSlug(string $title, string $locale): string
    {
        $baseSlug = Str::slug($title);
        
        // If model doesn't have ID yet (creating), return just the base slug
        // It will be updated after creation with the ID
        if (!$this->exists) {
            return $baseSlug;
        }
        
        return $this->id . '-' . $baseSlug;
    }

    /**
     * Update slugs after creation to include ID
     */
    public function updateSlugsWithId(): void
    {
        if (!$this->exists) {
            return;
        }

        $availableLocales = array_keys(config('app.available_locales'));
        $slugs = is_array($this->slug) ? $this->slug : [];

        foreach ($availableLocales as $locale) {
            $title = $this->getTranslation('title', $locale);
            if ($title) {
                $baseSlug = Str::slug($title);
                $slugs[$locale] = $this->id . '-' . $baseSlug;
            }
        }

        $this->updateQuietly(['slug' => $slugs]);
    }

    /**
     * Get slug for specific locale with ID prefix
     */
    public function getLocalizedSlug(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        return $this->getTranslation('slug', $locale);
    }

    /**
     * Get base slug for specific locale without ID prefix
     */
    public function getLocalizedBaseSlug(?string $locale = null): ?string
    {
        $fullSlug = $this->getLocalizedSlug($locale);
        if (!$fullSlug || !str_contains($fullSlug, '-')) {
            return $fullSlug;
        }
        
        // Remove ID prefix (e.g., "1-test-post" -> "test-post")
        $parts = explode('-', $fullSlug, 2);
        if (count($parts) >= 2 && is_numeric($parts[0])) {
            return $parts[1];
        }
        
        return $fullSlug;
    }

    /**
     * Get base slugs for all available locales
     *
     * @return array<string, string>
     */
    public function getAllBaseSlugs(): array
    {
        $baseSlugs = [];
        $availableLocales = array_keys(config('app.available_locales'));

        foreach ($availableLocales as $locale) {
            $baseSlugs[$locale] = $this->getLocalizedBaseSlug($locale) ?? '';
        }

        return $baseSlugs;
    }

    /**
     * Set base slug for specific locale (will be prefixed with ID automatically)
     */
    public function setLocalizedBaseSlug(string $baseSlug, ?string $locale = null): void
    {
        $locale = $locale ?: app()->getLocale();
        $slugs = is_array($this->slug) ? $this->slug : [];
        
        // Add ID prefix to base slug if model exists
        if ($this->exists) {
            $slugs[$locale] = $this->id . '-' . $baseSlug;
            $this->updateQuietly(['slug' => $slugs]);
        } else {
            // For new models, store without ID (will be added after creation)
            $slugs[$locale] = $baseSlug;
            $this->slug = $slugs;
        }
    }

    /**
     * Get route key for current locale (slug with ID prefix)
     */
    public function getRouteKey(): string
    {
        return $this->getLocalizedSlug() ?: (string) $this->id;
    }

    /**
     * Find model by slug in current locale
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?: app()->getLocale();
        
        // PostgreSQL JSONB operator to match by localized slug
        return static::whereRaw("slug->>? = ?", [$locale, $slug])->first();
    }

    /**
     * Resolve route model binding for ID-prefixed slugs
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // If it's numeric, treat as ID
        if (is_numeric($value)) {
            return $this->where('id', $value)->first();
        }

        // If it contains '-', extract ID from slug prefix
        if (str_contains($value, '-')) {
            $parts = explode('-', $value, 2);
            
            // Check if first part is numeric (ID)
            if (count($parts) >= 2 && is_numeric($parts[0])) {
                $id = (int) $parts[0];
                return $this->where('id', $id)->first();
            }
            
            // Fallback: try to find by slug (for backward compatibility)
            return $this->findBySlug($value);
        }

        // Fallback to default behavior
        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * Get the route key name for the model
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}