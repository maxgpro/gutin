<?php

use App\Models\BlogPost;
use App\Models\User;
use App\Models\BlogCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = BlogCategory::factory()->create();
    $this->user = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
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

test('author can delete their own post', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'blog_category_id' => $this->category->id
    ]);

    $response = $this->actingAs($this->user)->delete(route('blog.posts.destroy', $post));
    $response->assertRedirect(route('blog.posts.index'));

    $this->assertDatabaseMissing('blog_posts', [
        'id' => $post->id
    ]);
});

test('admin can delete any post', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'blog_category_id' => $this->category->id
    ]);

    $response = $this->actingAs($this->admin)->delete(route('blog.posts.destroy', $post));
    $response->assertRedirect(route('blog.posts.index'));

    $this->assertDatabaseMissing('blog_posts', [
        'id' => $post->id
    ]);
});

test('other user cannot delete post', function () {
    $otherUser = User::factory()->create();
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'blog_category_id' => $this->category->id
    ]);

    $response = $this->actingAs($otherUser)->delete(route('blog.posts.destroy', $post));
    $response->assertStatus(403);

    $this->assertDatabaseHas('blog_posts', [
        'id' => $post->id
    ]);
});

test('guest cannot delete post', function () {
    $post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'blog_category_id' => $this->category->id
    ]);

    $response = $this->delete(route('blog.posts.destroy', $post));
    $response->assertRedirect(route('login'));

    $this->assertDatabaseHas('blog_posts', [
        'id' => $post->id
    ]);
});
