<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
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
            'categories' => $categories,
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
            ->with('success', 'Category created successfully.');
    }

    public function show(BlogCategory $category)
    {
        $this->authorize('view', $category);

        $category->load(['posts' => function ($query) {
            $query->published()->with('user')->latest('published_at');
        }]);

        return Inertia::render('Blog/Categories/Show', [
            'category' => $category,
        ]);
    }

    public function edit(BlogCategory $category)
    {
        // Админский middleware уже проверил права
        return Inertia::render('Blog/Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(BlogCategoryUpdateRequest $request, BlogCategory $category)
    {
        $category->update($request->validated());

        return redirect()->route('blog.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(BlogCategory $category)
    {
        $this->authorize('delete', $category);

        if ($category->posts()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing posts.');
        }

        $category->delete();

        return redirect()->route('blog.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
