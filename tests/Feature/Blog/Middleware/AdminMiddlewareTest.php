<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['is_admin' => false]);
    $this->admin = User::factory()->create(['is_admin' => true]);
});

test('admin middleware allows admin access', function () {
    $response = $this->actingAs($this->admin)->get(route('blog.categories.create'));
    $response->assertStatus(200);
});

test('admin middleware blocks regular user', function () {
    $response = $this->actingAs($this->user)->get(route('blog.categories.create'));
    $response->assertStatus(403);
    $response->assertSeeText('Access denied. Admin rights required.');
});

test('admin middleware redirects guest to login', function () {
    $response = $this->get(route('blog.categories.create'));
    $response->assertRedirect(route('login'));
});

test('middleware protects all category management routes', function () {
    $category = \App\Models\BlogCategory::factory()->create();

    // Test all protected routes as regular user
    $routes = [
        ['get', route('blog.categories.create'), []],
        ['post', route('blog.categories.store'), ['name' => 'Test', 'color' => '#000000']],
        ['get', route('blog.categories.edit', $category), []],
        ['put', route('blog.categories.update', $category), ['name' => 'Test', 'color' => '#000000']],
        ['delete', route('blog.categories.destroy', $category), []],
    ];

    foreach ($routes as $routeData) {
        [$method, $route, $data] = $routeData;
        $response = $this->actingAs($this->user)->$method($route, $data);
        $response->assertStatus(403);
    }
});

test('middleware allows admin access to all category routes', function () {
    $category = \App\Models\BlogCategory::factory()->create();

    // Test create page
    $response = $this->actingAs($this->admin)->get(route('blog.categories.create'));
    $response->assertStatus(200);

    // Test edit page  
    $response = $this->actingAs($this->admin)->get(route('blog.categories.edit', $category));
    $response->assertStatus(200);
});
