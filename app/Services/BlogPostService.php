<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Http\Requests\Blog\BlogPostIndexRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class BlogPostService
{
    /**
     * Get paginated blog posts with filters, sorting and search
     */
    public function getFilteredPosts(BlogPostIndexRequest $request): LengthAwarePaginator
    {
        $perPage = (int) $request->integer('per_page', config('ui.posts_per_page', 6));
        
        $query = BlogPost::with(['user', 'category']);
        
        $this->applyStatusFilter($query, $request);
        $this->applySorting($query, $request);
        $this->applyCategoryFilter($query, $request);
        $this->applySearch($query, $request);
        
        return $query->paginate($perPage);
    }

    // getActiveCategories moved to BlogCategoryService

    /**
     * Apply status filter to query
     */
    protected function applyStatusFilter(Builder $query, BlogPostIndexRequest $request): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $isAdmin = $user && $user->isAdmin();
        
        if ($request->has('status') && in_array($request->status, BlogPost::STATUSES)) {
            // Если статус передан и валиден, применяем фильтр
            // (валидация прав происходит в Request)
            $query->where('status', $request->status);
        } elseif (!$isAdmin) {
            // Не-админы видят только опубликованные посты
            $query->published();
        }
        // Админы без фильтра статуса видят ВСЕ посты (ничего не добавляем к query)
    }

    /**
     * Apply sorting to query
     */
    protected function applySorting(Builder $query, BlogPostIndexRequest $request): void
    {
        $sortBy = $request->get('sort_by', 'published_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'published_at') {
            if ($sortOrder === 'asc') {
                $query->orderBy('published_at', 'asc')->orderBy('created_at', 'asc');
            } else {
                $query->latest('published_at')->latest('created_at');
            }
        } elseif ($sortBy === 'created_at') {
            if ($sortOrder === 'asc') {
                $query->orderBy('created_at', 'asc');
            } else {
                $query->latest('created_at');
            }
        } else {
            // Default sorting
            if ($request->has('status') && $request->status === BlogPost::STATUS_DRAFT) {
                $query->latest('created_at');
            } else {
                $query->latest('published_at');
            }
        }
    }

    /**
     * Apply category filter to query
     */
    protected function applyCategoryFilter(Builder $query, BlogPostIndexRequest $request): void
    {
        if ($request->filled('category_id')) {
            $query->where('blog_category_id', (int) $request->integer('category_id'));
        }
    }

    /**
     * Apply search filter to query
     */
    protected function applySearch(Builder $query, BlogPostIndexRequest $request): void
    {
        if ($request->has('search')) {
            $search = $request->search;
            $locale = app()->getLocale();
            $like = '%' . $search . '%';
            $query->where(function ($q) use ($locale, $like) {
                $q->whereRaw('title->>? ILIKE ?', [$locale, $like])
                    ->orWhereRaw('content->>? ILIKE ?', [$locale, $like])
                    ->orWhereRaw('excerpt->>? ILIKE ?', [$locale, $like]);
            });
        }
    }

    /**
     * Get related posts for a specific post
     */
    public function getRelatedPosts(BlogPost $post, int $limit = 3): Collection
    {
        return BlogPost::published()
            ->where('blog_category_id', $post->blog_category_id)
            ->where('id', '!=', $post->id)
            ->with(['user', 'category'])
            ->take($limit)
            ->get();
    }

    /**
     * Create a new blog post
     */
    public function createPost(array $data): BlogPost
    {
        if ($data['status'] === BlogPost::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Extract base slug if provided
        $hasSlugKey = array_key_exists('slug', $data);
        $baseSlug = $hasSlugKey ? (string) $data['slug'] : null;
        unset($data['slug']); // Remove from data as trait will handle slug generation
        
        $post = BlogPost::create($data);

        // Set base slug after creation
        if ($hasSlugKey) {
            if (is_string($baseSlug) && trim($baseSlug) !== '') {
                // Provided non-empty base slug
                $post->setLocalizedBaseSlug(trim($baseSlug));
                $post->save();
            } else {
                // Empty -> generate from title for current locale
                $title = $post->getTranslation('title', app()->getLocale());
                if ($title) {
                    $post->setLocalizedBaseSlug(\Illuminate\Support\Str::slug($title));
                    $post->save();
                }
            }
        }
        
        return $post;
    }

    /**
     * Update an existing blog post
     */
    public function updatePost(BlogPost $post, array $data): BlogPost
    {
        if ($data['status'] === BlogPost::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Extract base slug if provided (may be empty string)
        $hasSlugKey = array_key_exists('slug', $data);
        $baseSlug = $hasSlugKey ? (string) $data['slug'] : null;
        unset($data['slug']);

        // Update other fields first (may regenerate slug from title via trait hooks)
        $post->update($data);

        // If slug key was provided
        if ($hasSlugKey) {
            // If non-empty -> set explicitly
            if (trim($baseSlug) !== '') {
                $post->setLocalizedBaseSlug(trim($baseSlug));
                $post->save();
            } else {
                // Empty base slug -> regenerate from title for current locale
                // Take current locale title and set base slug using Str::slug
                $title = $post->getTranslation('title', app()->getLocale());
                if ($title) {
                    $post->setLocalizedBaseSlug(\Illuminate\Support\Str::slug($title));
                    $post->save();
                }
            }
        }
        
        return $post->fresh();
    }

    /**
     * Increment post views count
     */
    public function incrementViews(BlogPost $post): void
    {
        if ($post->isPublished()) {
            $post->incrementViews();
        }
    }
}