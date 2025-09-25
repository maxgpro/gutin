<?php

use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->activeCategory = BlogCategory::factory()->create(['is_active' => true]);
    $this->inactiveCategory = BlogCategory::factory()->create(['is_active' => false]);
});

test('anyone can view any categories', function () {
    $this->assertTrue($this->user->can('viewAny', BlogCategory::class));
    $this->assertTrue($this->admin->can('viewAny', BlogCategory::class));
});

test('anyone can view active category', function () {
    $this->assertTrue($this->user->can('view', $this->activeCategory));
    $this->assertTrue($this->admin->can('view', $this->activeCategory));
});

test('only admin can view inactive category', function () {
    $this->assertFalse($this->user->can('view', $this->inactiveCategory));
    $this->assertTrue($this->admin->can('view', $this->inactiveCategory));
});

test('guest can view active category', function () {
    $policy = new \App\Policies\BlogCategoryPolicy();
    $this->assertTrue($policy->view(null, $this->activeCategory));
});

test('guest cannot view inactive category', function () {
    $policy = new \App\Policies\BlogCategoryPolicy();
    $this->assertFalse($policy->view(null, $this->inactiveCategory));
});

test('only admin can create categories', function () {
    $this->assertFalse($this->user->can('create', BlogCategory::class));
    $this->assertTrue($this->admin->can('create', BlogCategory::class));
});

test('only admin can update categories', function () {
    $this->assertFalse($this->user->can('update', $this->activeCategory));
    $this->assertTrue($this->admin->can('update', $this->activeCategory));
});

test('only admin can delete categories', function () {
    $this->assertFalse($this->user->can('delete', $this->activeCategory));
    $this->assertTrue($this->admin->can('delete', $this->activeCategory));
});

test('only admin can restore categories', function () {
    $this->assertFalse($this->user->can('restore', $this->activeCategory));
    $this->assertTrue($this->admin->can('restore', $this->activeCategory));
});

test('only admin can force delete categories', function () {
    $this->assertFalse($this->user->can('forceDelete', $this->activeCategory));
    $this->assertTrue($this->admin->can('forceDelete', $this->activeCategory));
});
