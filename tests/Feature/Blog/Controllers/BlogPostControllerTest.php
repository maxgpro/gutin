<?php

use App\Models\BlogPost;
use App\Models\User;
use App\Models\BlogCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = BlogCategory::factory()->create();
    $this->user = User::factory()->create(['is_admin' => false]);
    $this->admin = User::factory()->create(['is_admin' => true]);
});

test('regular user cannot access create post page', function () {
    $response = $this->actingAs($this->user)->get(route('blog.posts.create'));
    $response->assertStatus(403);
});

test('admin can access create post page', function () {
    $response = $this->actingAs($this->admin)->get(route('blog.posts.create'));
    $response->assertStatus(200);
});

test('guest cannot access create post page', function () {
    $response = $this->get(route('blog.posts.create'));
    $response->assertRedirect(route('login'));
});

test('regular user cannot create post', function () {
    $postData = [
        'title' => 'Test Post',
        'content' => 'Test content',
        'blog_category_id' => $this->category->id,
        'status' => 'published'
    ];

    $response = $this->actingAs($this->user)->post(route('blog.posts.store'), $postData);
    $response->assertStatus(403);
});

test('admin can create post', function () {
    $postData = [
        'title' => 'Test Post',
        'content' => 'Test content',
        'blog_category_id' => $this->category->id,
        'status' => 'published'
    ];

    $response = $this->actingAs($this->admin)->post(route('blog.posts.store'), $postData);
    $response->assertRedirect();

    $this->assertDatabaseHas('blog_posts', [
        'title' => 'Test Post',
        'user_id' => $this->admin->id
    ]);
});
