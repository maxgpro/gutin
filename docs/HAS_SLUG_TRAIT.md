# HasSlug Trait Documentation

## Overview

The `HasSlug` trait provides **unified multilingual slug functionality** for Laravel models that use the Spatie Translatable package. It automatically generates ID-prefixed slugs for SEO-friendly URLs with guaranteed uniqueness.

**Location:** `app/Models/Traits/HasSlug.php`

## Features

- **ID-Prefixed Slugs**: Format `{id}-{localized-slug}` for guaranteed uniqueness
- **Multilingual Support**: Automatic slug generation for all configured locales
- **Route Model Binding**: Custom route binding that works with both ID-prefixed slugs and numeric IDs
- **Zero Configuration**: Works out of the box with standard `title` field
- **Complete Unification**: Same implementation for BlogPost and BlogCategory models

## Requirements

- Model must use `Spatie\Translatable\HasTranslations` trait
- Model must have `slug` and `title` fields in `$translatable` array
- Model must have `slug` field with `array` cast for JSONB storage

## Usage

### Unified Implementation

Both `BlogPost` and `BlogCategory` now use identical implementations:

```php
<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class BlogPost extends Model
{
    use HasTranslations, HasSlug;
    
    public $translatable = ['title', 'slug', 'excerpt', 'content'];
    
    protected $casts = [
        'title' => 'array',
        'slug' => 'array',
        // ... other casts
    ];
}

class BlogCategory extends Model
{
    use HasTranslations, HasSlug;
    
    public $translatable = ['title', 'slug', 'description'];
    
    protected $casts = [
        'title' => 'array',
        'slug' => 'array',
        'description' => 'array',
    ];
}
```

### Migration Setup

```php
Schema::create('blog_posts', function (Blueprint $table) {
    $table->id();
    $table->json('title');
    $table->json('slug');
    // ... other fields
    $table->timestamps();
});
```

## Features Provided

### Automatic Slug Generation
- Generates slugs on model creation and title updates
- Creates slugs for all configured locales
- Uses format: `{id}-{localized-slug}`

### Route Model Binding
- Handles ID-prefixed slugs: `/posts/123-my-awesome-post`
- Backward compatible with numeric IDs: `/posts/123`
- Falls back to slug-only search for compatibility

### Helper Methods
- `getLocalizedSlug(?string $locale = null)` - Get slug for specific locale
- `getRouteKey()` - Get current locale's slug for route generation
- `findBySlug(string $slug, ?string $locale = null)` - Find model by slug
- `updateSlugsWithId()` - Update slugs after creation with ID prefix

## Generated URLs

For a post with ID 123 and titles:
- English: "How to Build Apps"
- Russian: "Как создавать приложения"

The trait generates:
- English URL: `/posts/123-how-to-build-apps`
- Russian URL: `/posts/123-kak-sozdavat-prilozeniia`

## Generated Examples

### BlogPost Example
For a post with ID 123 and title "How to Build Laravel Apps":
- **URL**: `/posts/123-how-to-build-laravel-apps`
- **Multilingual**: Works in all configured locales

### BlogCategory Example  
For a category with ID 5 and title "Web Development":
- **URL**: `/categories/5-web-development`
- **Consistent Format**: Same ID-prefixed approach as posts

## Zero Configuration Benefits

### Before Unification (Old Approach)
- BlogPost: Used `title` field with custom logic
- BlogCategory: Used `name` field with method overrides
- Different implementations requiring maintenance

### After Unification (Current)
- **Both models**: Use identical `title` field
- **Zero overrides**: Trait works out-of-the-box
- **Consistent URLs**: Same `{id}-{slug}` format everywhere
- **Simplified maintenance**: Single implementation to maintain

## Customization Options

### Custom Source Field (If Needed)
If you need to use a different field than `title` for slug generation:

        static::updating(function ($model) {
            if ($model->isDirty('name')) {
                $model->generateSlugsForAllLocales();
            }
        });

        static::updated(function ($model) {
            if ($model->wasChanged('name')) {
                $model->updateSlugsWithId();
            }
        });
    }

    // Override to use 'name' field in updateSlugsWithId
    public function updateSlugsWithId(): void
    {
        if (!$this->exists) {
            return;
        }

        $availableLocales = array_keys(config('app.available_locales'));
        $slugs = is_array($this->slug) ? $this->slug : [];

        foreach ($availableLocales as $locale) {
            $name = $this->getTranslation('name', $locale);
            if ($name) {
                $baseSlug = Str::slug($name);
                $slugs[$locale] = $this->id . '-' . $baseSlug;
            }
        }

        $this->updateQuietly(['slug' => $slugs]);
    }
}
// Generates: 123-web-development, 124-web-development (same name, different IDs)
```

### Boot Method Integration

The trait automatically hooks into model events:
- `creating`: Generate initial slugs
- `created`: Update slugs with ID prefix
- `updating`: Regenerate slugs if title changed
- `updated`: Update slugs with ID if title changed

## Methods Available

### Public Methods
- `generateSlugsForAllLocales(): void`
- `updateSlugsWithId(): void`
- `getLocalizedSlug(?string $locale = null): ?string`
- `getRouteKey(): string`
- `getRouteKeyName(): string`
- `resolveRouteBinding($value, $field = null)`

### Static Methods
- `findBySlug(string $slug, ?string $locale = null): ?static`

## Database Queries

The trait uses PostgreSQL JSONB operators for efficient slug searches:

```sql
-- Find by slug in specific locale
SELECT * FROM posts WHERE slug->>'en' = '123-my-post';

-- Order by localized title
SELECT * FROM posts ORDER BY title->>'en' ASC;
```

## Benefits

1. **SEO-Friendly**: Descriptive URLs in multiple languages
2. **Performance**: ID-based lookups are faster than JSONB searches
3. **Uniqueness**: ID prefix ensures unique URLs across locales
4. **Compatibility**: Works with existing numeric ID routes
5. **Reusable**: Single trait can be used by multiple models

## Management Commands

Update existing model slugs:

```bash
php artisan blog:update-slugs
```

## Testing

The trait is fully tested with comprehensive test coverage including:
- Slug generation with ID prefixes
- Route model binding with different URL formats
- Multilingual URL resolution
- Backward compatibility with numeric IDs
- Error handling for invalid slugs