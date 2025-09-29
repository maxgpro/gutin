<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Http\Resources\BlogCategoryResource;
use App\Http\Resources\BlogPostResource;
use App\Services\BlogCategoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Blog\BlogCategoryStoreRequest;
use App\Http\Requests\Blog\BlogCategoryUpdateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BlogCategoryController extends Controller
{
    use AuthorizesRequests;
    
    public function __construct(
        protected BlogCategoryService $categoryService
    ) {}
    public function index()
    {
        $this->authorize('viewAny', BlogCategory::class);

        $categories = $this->categoryService->getList();

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
        $this->categoryService->createCategory($request->validated());

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
                'base_slug' => $category->getAllBaseSlugs(),
                'description' => $category->getTranslations('description'),
                'color' => $category->color,
                'is_active' => $category->is_active,
            ],
        ]);
    }

    public function update(BlogCategoryUpdateRequest $request, BlogCategory $category)
    {
        $this->categoryService->updateCategory($category, $request->validated());

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

    // Helper removed — now provided by HasSlug::getAllBaseSlugs()
}
