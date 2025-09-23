<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BlogPostController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 12);

        $query = BlogPost::with(['user', 'category'])
            ->latest('created_at');

        // Filter by status for admin/author view
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->published();
        }

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate($perPage);
        $categories = BlogCategory::where('is_active', true)->get();

        return Inertia::render('Blog/Posts/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'filters' => $request->only(['status', 'category', 'search']),
        ]);
    }

    public function create()
    {
        $categories = BlogCategory::where('is_active', true)->get();

        return Inertia::render('Blog/Posts/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'meta_data' => 'nullable|array',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
        ]);

        $validated['user_id'] = Auth::id();

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($validated);

        return redirect()->route('blog.posts.show', $post)
            ->with('success', 'Post created successfully.');
    }

    public function show(BlogPost $post)
    {
        $post->load(['user', 'category']);
        
        // Increment views for published posts
        if ($post->isPublished()) {
            $post->incrementViews();
        }

        // Get related posts
        $relatedPosts = BlogPost::published()
            ->where('blog_category_id', $post->blog_category_id)
            ->where('id', '!=', $post->id)
            ->with(['user', 'category'])
            ->take(3)
            ->get();

        return Inertia::render('Blog/Posts/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }

    public function edit(BlogPost $post)
    {
        // Basic authorization - only author or admin can edit
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }

        $categories = BlogCategory::where('is_active', true)->get();

        return Inertia::render('Blog/Posts/Edit', [
            'post' => $post,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, BlogPost $post)
    {
        // Basic authorization - only author or admin can edit
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $post->id,
            'blog_category_id' => 'required|exists:blog_categories,id',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string',
            'meta_data' => 'nullable|array',
            'status' => 'required|in:draft,published,archived',
            'published_at' => 'nullable|date',
        ]);

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        return redirect()->route('blog.posts.show', $post)
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(BlogPost $post)
    {
        // Basic authorization - only author or admin can delete
        if ($post->user_id !== Auth::id()) {
            abort(403);
        }

        $post->delete();

        return redirect()->route('blog.posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}