<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Services\BlogPostService;
use App\Http\Resources\BlogPostResource;
use App\Http\Resources\BlogCategoryResource;
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
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return Inertia::render('Blog/Posts/Index', [
            'posts' => $posts->through(fn ($post) => BlogPostResource::make($post)->toArray(request())),
            // Для фильтра по категориям достаточно локализованных значений
            'categories' => BlogCategoryResource::collection($categories)->toArray(request()),
            'filters' => $request->only(['status', 'category', 'search', 'sort_by', 'sort_order']),
            'statuses' => BlogPost::STATUSES,
            'canCreate' => $user ? Gate::allows('create', BlogPost::class) : false,
            'canFilterByStatus' => $user?->isAdmin() ?? false,
        ]);
    }

    public function create()
    {
        $this->authorize('create', BlogPost::class);

        $categories = $this->blogPostService->getActiveCategories();

        return Inertia::render('Blog/Posts/Create', [
            // Для формы создания списка категорий достаточно локализованных названий
            'categories' => BlogCategoryResource::collection($categories)->toArray(request()),
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
            'post' => BlogPostResource::make($post)->toArray(request()),
            'relatedPosts' => BlogPostResource::collection($relatedPosts)->toArray(request()),
            'canEdit' => Auth::user() ? Gate::allows('update', $post) : false,
            'canDelete' => Auth::user() ? Gate::allows('delete', $post) : false,
        ]);
    }

    public function edit(BlogPost $post)
    {
        $this->authorize('update', $post);

        $categories = $this->blogPostService->getActiveCategories();

        return Inertia::render('Blog/Posts/Edit', [
            // Для формы редактирования нужен полный набор переводов поста
            'post' => [
                'id' => $post->id,
                'title' => $post->getTranslations('title'),
                'slug' => $post->getTranslations('slug'),
                'excerpt' => $post->getTranslations('excerpt'),
                'content' => $post->getTranslations('content'),
                'blog_category_id' => $post->blog_category_id,
                'featured_image' => $post->featured_image,
                'status' => $post->status,
                'published_at' => optional($post->published_at)->toDateTimeString(),
            ],
            // и локализованные категории для селекта
            'categories' => BlogCategoryResource::collection($categories),
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
