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
            ->orderByLocalizedName()
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
        BlogCategory::create($request->validated());

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
        // Админский middleware уже проверил права
        return Inertia::render('Blog/Categories/Edit', [
            // Для формы редактирования удобнее отдать все переводы
            // чтобы сохранить возможность локализованного ввода на фронте при необходимости
            'category' => [
                'id' => $category->id,
                'name' => $category->getTranslations('name'),
                'slug' => $category->getTranslations('slug'),
                'description' => $category->getTranslations('description'),
                'color' => $category->color,
                'is_active' => $category->is_active,
            ],
        ]);
    }

    public function update(BlogCategoryUpdateRequest $request, BlogCategory $category)
    {
        $category->update($request->validated());

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
}
