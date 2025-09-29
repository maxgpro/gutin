<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Http\Resources\BlogCategoryResource;
use App\Http\Resources\BlogPostResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Blog\BlogCategoryStoreRequest;
use App\Http\Requests\Blog\BlogCategoryUpdateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BlogCategoryController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny', BlogCategory::class);

        $categories = BlogCategory::withCount('posts')
            ->orderByLocalizedTitle()
            ->get();

        return Inertia::render('Blog/Categories/Index', [
            'categories' => BlogCategoryResource::collection($categories)->toArray(request()),
            'canCreate' => Auth::user() ? Gate::allows('create', BlogCategory::class) : false,
        ]);
    }

    public function create()
    {
        $this->authorize('create', BlogCategory::class);

        return Inertia::render('Blog/Categories/Create');
    }

    public function store(BlogCategoryStoreRequest $request)
    {
        $data = $request->validated();

        // Extract base slug if provided (may be empty)
        $hasSlugKey = array_key_exists('slug', $data);
        $baseSlug = $hasSlugKey ? (string) $data['slug'] : null;
        unset($data['slug']); // Remove from data as trait will handle slug generation
        
        $category = BlogCategory::create($data);

        // Set base slug after creation
        if ($hasSlugKey) {
            if (is_string($baseSlug) && trim($baseSlug) !== '') {
                $category->setLocalizedBaseSlug(trim($baseSlug));
                $category->save();
            } else {
                $title = $category->getTranslation('title', app()->getLocale());
                if ($title) {
                    $category->setLocalizedBaseSlug(\Illuminate\Support\Str::slug($title));
                    $category->save();
                }
            }
        }

        return redirect()->route('blog.categories.index')
            ->with('success', __('ui.category_created'));
    }

    public function show(BlogCategory $category)
    {
        $this->authorize('view', $category);

        $category->load(['posts' => function ($query) {
            $query->published()->with('user')->latest('published_at');
        }]);

        return Inertia::render('Blog/Categories/Show', [
            'category' => array_merge(
                BlogCategoryResource::make($category)->toArray(request()),
                [
                    // Локализованные посты категории
                    'posts' => BlogPostResource::collection($category->posts)->toArray(request()),
                ]
            ),
        ]);
    }

    public function edit(BlogCategory $category)
    {
        return Inertia::render('Blog/Categories/Edit', [
            'category' => [
                'id' => $category->id,
                'title' => $category->getTranslations('title'),
                'slug' => $category->getTranslations('slug'),
                'base_slug' => $this->getBaseSlugs($category),
                'description' => $category->getTranslations('description'),
                'color' => $category->color,
                'is_active' => $category->is_active,
            ],
        ]);
    }

    public function update(BlogCategoryUpdateRequest $request, BlogCategory $category)
    {
        $data = $request->validated();

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
                    $category->setLocalizedBaseSlug(\Illuminate\Support\Str::slug($title));
                    $category->save();
                }
            }
        }

        return redirect()->route('blog.categories.index')
            ->with('success', __('ui.category_updated'));
    }

    public function destroy(BlogCategory $category)
    {
        $this->authorize('delete', $category);

        if ($category->posts()->count() > 0) {
            return back()->with('error', __('ui.cannot_delete_with_posts'));
        }

        $category->delete();

        return redirect()->route('blog.categories.index')
            ->with('success', __('ui.category_deleted'));
    }

    /**
     * Get base slugs for all locales
     */
    private function getBaseSlugs($model): array
    {
        $baseSlugs = [];
        $availableLocales = array_keys(config('app.available_locales'));
        
        foreach ($availableLocales as $locale) {
            $baseSlugs[$locale] = $model->getLocalizedBaseSlug($locale) ?? '';
        }
        
        return $baseSlugs;
    }
}
