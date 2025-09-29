<?php

use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->admin = User::factory()->admin()->create();
    $this->category = BlogCategory::factory()->create();
});

test('anyone can access categories index', function () {
    // Неавторизованный пользователь
    $response = $this->get(route('blog.categories.index'));
    $response->assertStatus(200);

    // Обычный пользователь
    $response = $this->actingAs($this->user)->get(route('blog.categories.index'));
    $response->assertStatus(200);

    // Админ
    $response = $this->actingAs($this->admin)->get(route('blog.categories.index'));
    $response->assertStatus(200);
});

test('regular user cannot access create category page', function () {
    $response = $this->actingAs($this->user)->get(route('blog.categories.create'));
    $response->assertStatus(403);
});

test('admin can access create category page', function () {
    $response = $this->actingAs($this->admin)->get(route('blog.categories.create'));
    $response->assertStatus(200);
});

test('guest cannot access create category page', function () {
    $response = $this->get(route('blog.categories.create'));
    $response->assertRedirect(route('login'));
});

test('regular user cannot create category', function () {
    $categoryData = [
        'name' => 'Test Category',
        'color' => '#ff0000',
        'is_active' => true
    ];

    $response = $this->actingAs($this->user)->post(route('blog.categories.store'), $categoryData);
    $response->assertStatus(403);
});

test('admin can create category', function () {
    $categoryData = [
        'title' => 'Test Category',
        'color' => '#ff0000',
        'is_active' => true
    ];

    $response = $this->actingAs($this->admin)->post(route('blog.categories.store'), $categoryData);
    $response->assertRedirect();

    // Check that category was created
    $category = BlogCategory::where('color', '#ff0000')
        ->active()
        ->first();
    
    expect($category)->not->toBeNull();
    expect($category->title)->toBe('Test Category');
    expect($category->slug)->toBe($category->id . '-test-category');
});

test('regular user cannot access edit category page', function () {
    $response = $this->actingAs($this->user)->get(route('blog.categories.edit', $this->category));
    $response->assertStatus(403);
});

test('admin can access edit category page', function () {
    $response = $this->actingAs($this->admin)->get(route('blog.categories.edit', $this->category));
    $response->assertStatus(200);
});

test('regular user cannot update category', function () {
    $categoryData = [
        'name' => 'Updated Category',
        'color' => '#00ff00',
        'is_active' => false
    ];

    $response = $this->actingAs($this->user)->put(route('blog.categories.update', $this->category), $categoryData);
    $response->assertStatus(403);
});

test('admin can update category', function () {
    $categoryData = [
        'title' => 'Updated Category',
        'color' => '#00ff00',
        'is_active' => false
    ];

    $response = $this->actingAs($this->admin)->put(route('blog.categories.update', $this->category), $categoryData);
    $response->assertRedirect();

    // Check that category was updated
    $updatedCategory = BlogCategory::find($this->category->id);
    
    expect($updatedCategory)->not->toBeNull();
    expect($updatedCategory->title)->toBe('Updated Category');
    expect($updatedCategory->slug)->toBe($updatedCategory->id . '-updated-category');
    expect($updatedCategory->color)->toBe('#00ff00');
    expect($updatedCategory->is_active)->toBe(false);
});

test('regular user cannot delete category', function () {
    $response = $this->actingAs($this->user)->delete(route('blog.categories.destroy', $this->category));
    $response->assertStatus(403);
});

test('admin can delete category', function () {
    $response = $this->actingAs($this->admin)->delete(route('blog.categories.destroy', $this->category));
    $response->assertRedirect();

    $this->assertDatabaseMissing('blog_categories', [
        'id' => $this->category->id
    ]);
});
