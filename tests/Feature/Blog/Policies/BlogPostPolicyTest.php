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
    $this->post = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'blog_category_id' => $this->category->id,
        'status' => 'published',
        'published_at' => now()
    ]);
});

test('admin can view any post', function () {
    $this->assertTrue($this->admin->can('view', $this->post));
});

test('author can view their own post', function () {
    $this->assertTrue($this->user->can('view', $this->post));
});

test('author can update their own post', function () {
    $this->assertTrue($this->user->can('update', $this->post));
});

test('admin can update any post', function () {
    $this->assertTrue($this->admin->can('update', $this->post));
});

test('other user cannot update post', function () {
    $otherUser = User::factory()->create(['is_admin' => false]);
    $this->assertFalse($otherUser->can('update', $this->post));
});

test('author can delete their own post', function () {
    $this->assertTrue($this->user->can('delete', $this->post));
});

test('admin can delete any post', function () {
    $this->assertTrue($this->admin->can('delete', $this->post));
});

test('other user cannot delete post', function () {
    $otherUser = User::factory()->create(['is_admin' => false]);
    $this->assertFalse($otherUser->can('delete', $this->post));
});

// test('authenticated user can create posts', function () {
//     $this->assertTrue($this->user->can('create', BlogPost::class));
// });

test('admin can create posts', function () {
    $this->assertTrue($this->admin->can('create', BlogPost::class));
});

test('regular user cannot create posts', function () {
    $this->assertFalse($this->user->can('create', BlogPost::class));
});

test('guest cannot view draft post', function () {
    $draft = BlogPost::factory()->create([
        'user_id' => $this->user->id,
        'blog_category_id' => $this->category->id,
        'status' => 'draft'
    ]);

    // Тест политики для неавторизованного пользователя (null)
    $policy = new \App\Policies\BlogPostPolicy();
    $this->assertFalse($policy->view(null, $draft));
});

test('guest can view published post', function () {
    // Тест политики для неавторизованного пользователя (null)
    $policy = new \App\Policies\BlogPostPolicy();
    $this->assertTrue($policy->view(null, $this->post)); // $this->post is published
});

test('only admin can restore posts', function () {
    $this->assertTrue($this->admin->can('restore', $this->post));
    $this->assertFalse($this->user->can('restore', $this->post));
});

test('only admin can force delete posts', function () {
    $this->assertTrue($this->admin->can('forceDelete', $this->post));
    $this->assertFalse($this->user->can('forceDelete', $this->post));
});
