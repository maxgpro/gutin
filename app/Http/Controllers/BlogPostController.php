<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Services\BlogPostService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\Blog\BlogPostIndexRequest;
use App\Http\Requests\Blog\BlogPostStoreRequest;
use App\Http\Requests\Blog\BlogPostUpdateRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BlogPostController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected BlogPostService $blogPostService
    ) {}
    public function index(BlogPostIndexRequest $request)
    {
        $posts = $this->blogPostService->getFilteredPosts($request);
        $categories = $this->blogPostService->getActiveCategories();

        return Inertia::render('Blog/Posts/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'filters' => $request->only(['status', 'category', 'search', 'sort_by', 'sort_order']),
            'statuses' => BlogPost::STATUSES,
            'canCreate' => Auth::user() ? Gate::allows('create', BlogPost::class) : false,
        ]);
    }

    public function create()
    {
        $this->authorize('create', BlogPost::class);

        $categories = $this->blogPostService->getActiveCategories();

        return Inertia::render('Blog/Posts/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(BlogPostStoreRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        
        $post = $this->blogPostService->createPost($validated);

        return redirect()->route('blog.posts.show', $post)
            ->with('success', 'Post created successfully.');
    }

    public function show(BlogPost $post)
    {
        $this->authorize('view', $post);

        $post->load(['user', 'category']);

        // Increment views for published posts
        $this->blogPostService->incrementViews($post);

        // Get related posts
        $relatedPosts = $this->blogPostService->getRelatedPosts($post);

        return Inertia::render('Blog/Posts/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'canEdit' => Auth::user() ? Gate::allows('update', $post) : false,
            'canDelete' => Auth::user() ? Gate::allows('delete', $post) : false,
        ]);
    }

    public function edit(BlogPost $post)
    {
        $this->authorize('update', $post);

        $categories = $this->blogPostService->getActiveCategories();

        return Inertia::render('Blog/Posts/Edit', [
            'post' => $post,
            'categories' => $categories,
        ]);
    }

    public function update(BlogPostUpdateRequest $request, BlogPost $post)
    {
        $validated = $request->validated();
        
        $post = $this->blogPostService->updatePost($post, $validated);

        return redirect()->route('blog.posts.show', $post)
            ->with('success', 'Post updated successfully.');
    }

    public function destroy(BlogPost $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('blog.posts.index')
            ->with('success', 'Post deleted successfully.');
    }
}
