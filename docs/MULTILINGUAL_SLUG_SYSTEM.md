# Multilingual Slug System

## Overview

The blog system now supports multilingual slugs with ID prefixes for unique URL routing across different locales. This allows URLs like `/posts/1-test-post/` and `/posts/1-testovaya-statya/` to lead to the same post while supporting different languages.

## URL Format

- **New format**: `/blog/posts/{id}-{localized-slug}`
- **Examples**:
  - English: `/blog/posts/77-my-awesome-blog-post`
  - Russian: `/blog/posts/77-moia-krutaia-statia`
  - French: `/blog/posts/77-mon-super-article`
- **Backward compatibility**: `/blog/posts/{id}` still works

## Implementation Details

### BlogPost Model Changes

1. **Slug Generation**: 
   - `generateLocalizedSlug()` creates slugs in format `{id}-{slug}`
   - `updateSlugsWithId()` updates slugs after creation with ID prefix
   - Automatic slug generation on post creation and title updates

2. **Route Model Binding**: 
   - `resolveRouteBinding()` extracts ID from slug prefix
   - Supports both numeric IDs and ID-prefixed slugs
   - Falls back to legacy slug lookup for compatibility

3. **Helper Methods**:
   - `getLocalizedSlug($locale)` - Get slug for specific locale
   - `getRouteKey()` - Get current locale slug for route generation

### Database Storage

- Slugs stored in JSONB field with locale keys
- Format: `{"en": "77-my-post", "ru": "77-moy-post"}`
- PostgreSQL JSONB operators used for efficient queries

### Model Events

- `creating`: Generate initial slugs without ID
- `created`: Update slugs with ID prefix after creation
- `updating`: Regenerate slugs if title changed
- `updated`: Update slugs with ID if title was changed

## Usage Examples

### Creating Posts with Multilingual Titles

```php
$post = BlogPost::create([
    'title' => [
        'en' => 'My Awesome Post',
        'ru' => 'Моя крутая статья'
    ],
    'content' => [...],
    // Other fields...
]);

// After creation:
echo $post->getLocalizedSlug('en'); // "1-my-awesome-post"
echo $post->getLocalizedSlug('ru'); // "1-moia-krutaia-statia"
```

### Route Generation

```php
// In controllers/views - automatic via getRouteKey()
route('blog.posts.show', $post); // Uses current locale slug

// Direct slug access
$post->getLocalizedSlug('en'); // Get English slug
$post->getRouteKey(); // Get current locale slug
```

### Frontend Integration

```vue
<!-- BlogPostCard.vue - already works automatically -->
<TextLink :href="blog.posts.show(post)">
    {{ getLocalized(post.title) }}
</TextLink>
```

## Route Binding Logic

1. **Numeric ID**: Direct database lookup by ID
2. **ID-prefixed slug**: Extract ID from prefix, lookup by ID
3. **Legacy slug**: Fallback to JSONB slug search
4. **Invalid**: Return 404

## Benefits

- **SEO-friendly**: Descriptive URLs in multiple languages
- **Unique**: ID prefix ensures uniqueness across locales
- **Fast**: ID-based lookups are efficient
- **Multilingual**: Each locale gets native URL slug
- **Compatible**: Existing numeric IDs still work

## Management Commands

### Update Existing Slugs

For existing posts created before the slug system, use the management command:

```bash
php artisan blog:update-slugs
```

This command will:
- Update all existing blog posts to use ID-prefixed slugs
- Show progress bar for large datasets
- Handle existing posts safely without data loss

## Testing

The system includes comprehensive tests covering:
- Slug generation with ID prefixes
- Route model binding with different formats
- URL routing for multiple locales
- Backward compatibility with numeric IDs
- Error handling for invalid slugs

All tests pass: 153 tests with 645 assertions.