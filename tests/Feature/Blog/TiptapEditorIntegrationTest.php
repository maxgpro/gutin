<?php

use App\Models\BlogCategory;
use \App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->category = BlogCategory::factory()->create();
});

test('admin can create post with HTML content from tiptap editor', function () {
    $htmlContent = '<h2>Test Heading</h2><p>This is a <strong>rich text</strong> paragraph with <em>formatting</em>.</p><ul><li>Item 1</li><li>Item 2</li></ul>';

    $postData = [
        'title' => 'Rich Text Post',
        'content' => $htmlContent,
        'blog_category_id' => $this->category->id,
        'status' => BlogPost::STATUS_PUBLISHED,
    ];

    $response = $this->actingAs($this->admin)->post(route('blog.posts.store'), $postData);

    $response->assertRedirect();

    $this->assertDatabaseHas('blog_posts', [
        'title' => 'Rich Text Post',
        'content' => $htmlContent,
        'user_id' => $this->admin->id,
    ]);
});

test('admin can update post with HTML content from tiptap editor', function () {
    $post = \App\Models\BlogPost::factory()->create([
        'user_id' => $this->admin->id,
        'blog_category_id' => $this->category->id,
        'content' => '<p>Original content</p>',
    ]);

    $newHtmlContent = '<h1>Updated Heading</h1><p>Updated content with <strong>bold text</strong> and <code>inline code</code>.</p><blockquote>This is a quote</blockquote>';

    $updateData = [
        'title' => $post->title,
        'content' => $newHtmlContent,
        'blog_category_id' => $this->category->id,
        'status' => $post->status,
    ];

    $response = $this->actingAs($this->admin)->put(route('blog.posts.update', $post), $updateData);

    $response->assertRedirect();

    $this->assertDatabaseHas('blog_posts', [
        'id' => $post->id,
        'content' => $newHtmlContent
    ]);
});

test('post content is properly displayed with HTML formatting', function () {
    $htmlContent = '<h2>Test Heading</h2><p>Content with <strong>bold</strong> and <em>italic</em> text.</p><ul><li>List item 1</li><li>List item 2</li></ul>';

    $post = \App\Models\BlogPost::factory()->create([
        'blog_category_id' => $this->category->id,
        'status' => BlogPost::STATUS_PUBLISHED,
        'published_at' => now(),
        'content' => $htmlContent,
    ]);

    $response = $this->get(route('blog.posts.show', $post));

    $response->assertStatus(200);
    // Test that the text content is visible (HTML will be rendered)
    $response->assertSee('Test Heading');
    $response->assertSee('bold');
    $response->assertSee('italic');
    $response->assertSee('List item 1');
});
