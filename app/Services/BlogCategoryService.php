<?php

namespace App\Services;

use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class BlogCategoryService
{
    /**
     * Get categories list for index page
     */
    public function getList(): Collection
    {
        return BlogCategory::withCount('posts')
            ->orderByLocalizedTitle()
            ->get();
    }

    /**
     * Get active blog categories (for selectors)
     */
    public function getActiveCategories(): Collection
    {
        return BlogCategory::active()
            ->orderByLocalizedTitle()
            ->get();
    }

    /**
     * Create a new category with base_slug handling
     */
    public function createCategory(array $data): BlogCategory
    {
        // Extract base slug if provided (may be empty)
        $hasSlugKey = array_key_exists('slug', $data);
        $baseSlug = $hasSlugKey ? (string) $data['slug'] : null;
        unset($data['slug']);

        $category = BlogCategory::create($data);

        if ($hasSlugKey) {
            if (is_string($baseSlug) && trim($baseSlug) !== '') {
                $category->setLocalizedBaseSlug(trim($baseSlug));
                $category->save();
            } else {
                $title = $category->getTranslation('title', app()->getLocale());
                if ($title) {
                    $category->setLocalizedBaseSlug(Str::slug($title));
                    $category->save();
                }
            }
        }

        return $category;
    }

    /**
     * Update an existing category with base_slug handling
     */
    public function updateCategory(BlogCategory $category, array $data): BlogCategory
    {
        // Extract base slug if provided (may be empty)
        $hasSlugKey = array_key_exists('slug', $data);
        $baseSlug = $hasSlugKey ? (string) $data['slug'] : null;
        unset($data['slug']);

        $category->update($data);

        if ($hasSlugKey) {
            if (trim($baseSlug) !== '') {
                $category->setLocalizedBaseSlug(trim($baseSlug));
                $category->save();
            } else {
                $title = $category->getTranslation('title', app()->getLocale());
                if ($title) {
                    $category->setLocalizedBaseSlug(Str::slug($title));
                    $category->save();
                }
            }
        }

        return $category->fresh();
    }
}
