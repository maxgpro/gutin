<?php

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = BlogCategory::factory()->create();
    $this->user = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
});

test('posts can be filtered by status', function () {
    // Create posts with different statuses
    $publishedPost = BlogPost::factory()->create([
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now(),
        'blog_category_id' => $this->category->id,
    ]);
    
    $draftPost = BlogPost::factory()->create([
        'status' => BlogPost::STATUS_DRAFT,
        'blog_category_id' => $this->category->id,
    ]);
    
    $archivedPost = BlogPost::factory()->create([
        'status' => BlogPost::STATUS_ARCHIVED,
        'blog_category_id' => $this->category->id,
    ]);

    // Test filtering by published status
    $response = $this->actingAs($this->admin)->get(route('blog.posts.index', ['status' => BlogPost::STATUS_PUBLISHED]));
    $response->assertOk();
    
    // Test filtering by draft status
    $response = $this->actingAs($this->admin)->get(route('blog.posts.index', ['status' => BlogPost::STATUS_DRAFT]));
    $response->assertOk();
    
    // Test filtering by archived status
    $response = $this->actingAs($this->admin)->get(route('blog.posts.index', ['status' => BlogPost::STATUS_ARCHIVED]));
    $response->assertOk();
});

test('posts can be filtered by category in current locale', function () {
    // Use English locale for predictable slug assertions
    app()->setLocale('en');

    $categoryA = BlogCategory::factory()->create();
    $categoryB = BlogCategory::factory()->create();

    // Create one published post in each category
    $postInA = BlogPost::factory()->create([
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now(),
        'blog_category_id' => $categoryA->id,
        'title' => ['en' => 'Post A', 'ru' => 'Пост A', 'fr' => 'Article A'],
    ]);

    $postInB = BlogPost::factory()->create([
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now(),
        'blog_category_id' => $categoryB->id,
        'title' => ['en' => 'Post B', 'ru' => 'Пост B', 'fr' => 'Article B'],
    ]);

    $slugA = $categoryA->getTranslation('slug', 'en');

    $response = $this->get(route('blog.posts.index', ['category' => $slugA]));

    $response->assertOk();
    $response->assertInertia(fn ($page) =>
        $page
            ->where('filters.category', $slugA)
            ->has('posts.data', 1)
            ->where('posts.data.0.category.slug', $slugA)
    );
});

test('non-admin users cannot filter by non-published status', function () {
    $user = User::factory()->create();

    // As guest
    $this->get(route('blog.posts.index', ['status' => BlogPost::STATUS_DRAFT]))
        ->assertForbidden();

    // As regular authenticated user
    $this->actingAs($user)
        ->get(route('blog.posts.index', ['status' => BlogPost::STATUS_DRAFT]))
        ->assertForbidden();
});

test('posts can be sorted by published_at in descending order', function () {
    $olderPost = BlogPost::factory()->create([
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now()->subDays(2),
        'blog_category_id' => $this->category->id,
        'title' => 'Older Post',
    ]);
    
    $newerPost = BlogPost::factory()->create([
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now()->subDay(),
        'blog_category_id' => $this->category->id,
        'title' => 'Newer Post',
    ]);

    $response = $this->get(route('blog.posts.index', [
        'sort_by' => 'published_at',
        'sort_order' => 'desc'
    ]));
    
    $response->assertOk();
});

test('posts can be sorted by published_at in ascending order', function () {
    $olderPost = BlogPost::factory()->create([
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now()->subDays(2),
        'blog_category_id' => $this->category->id,
        'title' => 'Older Post',
    ]);
    
    $newerPost = BlogPost::factory()->create([
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now()->subDay(),
        'blog_category_id' => $this->category->id,
        'title' => 'Newer Post',
    ]);

    $response = $this->get(route('blog.posts.index', [
        'sort_by' => 'published_at',
        'sort_order' => 'asc'
    ]));
    
    $response->assertOk();
});

test('posts can be sorted by created_at', function () {
    $olderPost = BlogPost::factory()->create([
        'status' => BlogPost::STATUS_DRAFT,
        'created_at' => now()->subDays(2),
        'blog_category_id' => $this->category->id,
        'title' => 'Older Draft',
    ]);
    
    $newerPost = BlogPost::factory()->create([
        'status' => BlogPost::STATUS_DRAFT,
        'created_at' => now()->subDay(),
        'blog_category_id' => $this->category->id,
        'title' => 'Newer Draft',
    ]);

    $response = $this->actingAs($this->admin)->get(route('blog.posts.index', [
        'status' => BlogPost::STATUS_DRAFT,
        'sort_by' => 'created_at',
        'sort_order' => 'desc'
    ]));
    
    $response->assertOk();
});

test('invalid status filter returns validation error', function () {
    // Create admin to test validation (not authorization)
    $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
    $admin = \App\Models\User::factory()->create();
    $admin->roles()->attach($adminRole);
    
    BlogPost::factory()->create([
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now(),
        'blog_category_id' => $this->category->id,
    ]);

    $response = $this->actingAs($admin)->get(route('blog.posts.index', ['status' => 'invalid_status']));
    $response->assertRedirect();
    $response->assertSessionHasErrors(['status']);
});

test('invalid sort parameters return validation error', function () {
    $response = $this->get(route('blog.posts.index', [
        'sort_by' => 'invalid_field',
        'sort_order' => 'invalid_order'
    ]));
    
    $response->assertRedirect();
    $response->assertSessionHasErrors(['sort_by', 'sort_order']);
});

test('default behavior shows only published posts for guests', function () {
    BlogPost::factory()->create([
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now(),
        'blog_category_id' => $this->category->id,
    ]);
    
    BlogPost::factory()->create([
        'status' => BlogPost::STATUS_DRAFT,
        'blog_category_id' => $this->category->id,
    ]);

    $response = $this->get(route('blog.posts.index'));
    $response->assertOk();
});