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
        $perPage = (int) $request->integer('per_page', 12);
        
        $query = BlogPost::with(['user', 'category']);
        
        $this->applyStatusFilter($query, $request);
        $this->applySorting($query, $request);
        $this->applyCategoryFilter($query, $request);
        $this->applySearch($query, $request);
        
        return $query->paginate($perPage);
    }

    /**
     * Get active blog categories
     */
    public function getActiveCategories(): Collection
    {
        return BlogCategory::where('is_active', true)->get();
    }

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
        if ($request->has('category')) {
            $slug = $request->category;
            $locale = app()->getLocale();
            $query->whereHas('category', function ($q) use ($slug, $locale) {
                // Compare localized slug stored as JSONB
                $q->whereRaw('slug->>? = ?', [$locale, $slug]);
            });
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
        
        return BlogPost::create($data);
    }

    /**
     * Update an existing blog post
     */
    public function updatePost(BlogPost $post, array $data): BlogPost
    {
        if ($data['status'] === BlogPost::STATUS_PUBLISHED && empty($data['published_at'])) {
            $data['published_at'] = now();
        }
        
        $post->update($data);
        
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