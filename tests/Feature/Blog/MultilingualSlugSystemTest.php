<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->category = BlogCategory::factory()->create();
});

test('post creates slugs with ID prefix for all locales', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'blog_category_id' => $this->category->id,
        'title' => [
            'en' => 'Test Post',
            'ru' => 'Тестовая статья',
            'fr' => 'Article de test'
        ]
    ]);

    expect($post->getLocalizedSlug('en'))->toBe($post->id . '-test-post');
    expect($post->getLocalizedSlug('ru'))->toStartWith($post->id . '-');
    expect($post->getLocalizedSlug('fr'))->toStartWith($post->id . '-');
    
    // Check that all slugs contain the ID prefix
    expect($post->getLocalizedSlug('en'))->toContain('-test-post');
    expect($post->getLocalizedSlug('ru'))->toMatch('/^' . $post->id . '-.+$/');
    expect($post->getLocalizedSlug('fr'))->toMatch('/^' . $post->id . '-.+$/');
});

test('post can be found by ID-prefixed slug', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'blog_category_id' => $this->category->id,
        'title' => [
            'en' => 'Test Post',
            'ru' => 'Тестовая статья'
        ]
    ]);

    // Create instance to test route binding
    $model = new BlogPost();
    
    $foundByEnglish = $model->resolveRouteBinding($post->id . '-test-post');
    $foundByRussian = $model->resolveRouteBinding($post->id . '-any-slug');

    expect($foundByEnglish)->not->toBeNull();
    expect($foundByRussian)->not->toBeNull();
    expect($foundByEnglish->id)->toBe($post->id);
    expect($foundByRussian->id)->toBe($post->id);
});

test('post route binding works with different locale slugs', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'blog_category_id' => $this->category->id,
        'title' => [
            'en' => 'Test Post',
            'ru' => 'Тестовая статья'
        ],
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now()
    ]);

    // Test English slug URL
    $response = $this->get('/blog/posts/' . $post->id . '-test-post');
    $response->assertSuccessful();
    
    // Test any slug with correct ID 
    $response = $this->get('/blog/posts/' . $post->id . '-any-slug');
    $response->assertSuccessful();
    
    // Check Inertia props contain the post
    $props = $response->inertiaProps();
    expect($props['post']['id'])->toBe($post->id);
});

test('numeric ID still works for backward compatibility', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'blog_category_id' => $this->category->id,
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now()
    ]);

    $response = $this->get('/blog/posts/' . $post->id);
    $response->assertSuccessful();
    
    $props = $response->inertiaProps();
    expect($props['post']['id'])->toBe($post->id);
});

test('invalid slug returns 404', function () {
    $response = $this->get('/blog/posts/999-non-existent-post');
    $response->assertNotFound();
});

test('slug updates when title changes', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'blog_category_id' => $this->category->id,
        'title' => [
            'en' => 'Original Title'
        ]
    ]);

    $originalSlug = $post->getLocalizedSlug('en');
    expect($originalSlug)->toBe($post->id . '-original-title');

    // Update title
    $post->update([
        'title' => [
            'en' => 'Updated Title'
        ]
    ]);

    $post->refresh();
    $newSlug = $post->getLocalizedSlug('en');
    expect($newSlug)->toBe($post->id . '-updated-title');
});