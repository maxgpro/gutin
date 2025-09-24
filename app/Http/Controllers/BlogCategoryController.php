<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogCategoryController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $this->authorize('viewAny', BlogCategory::class);

        $categories = BlogCategory::withCount('posts')
            ->orderBy('name')
            ->get();

        return Inertia::render('Blog/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        // Админский middleware уже проверил права
        return Inertia::render('Blog/Categories/Create');
    }

    public function store(Request $request)
    {
        // Админский middleware уже проверил права
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_categories',
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        BlogCategory::create($validated);

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

    public function update(Request $request, BlogCategory $category)
    {
        // Админский middleware уже проверил права
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return redirect()->route('blog.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(BlogCategory $category)
    {
        // Админский middleware уже проверил права
        if ($category->posts()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing posts.');
        }

        $category->delete();

        return redirect()->route('blog.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
