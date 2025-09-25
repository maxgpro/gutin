<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

describe('Blog Categories Menu Visibility', function () {
    it('shows categories menu to admin', function () {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertInertia(
            fn(Assert $page) => $page
                ->has('auth.canCreateBlogCategories')
                ->where('auth.canCreateBlogCategories', true)
        );
    });

    it('hides categories menu from regular user', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn(Assert $page) => $page
                ->has('auth.canCreateBlogCategories')
                ->where('auth.canCreateBlogCategories', false)
        );
    });

    it('hides categories menu from mentee', function () {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        $mentee = User::factory()->mentee()->create();

        $response = $this->actingAs($mentee)->get(route('dashboard'));

        $response->assertInertia(
            fn(Assert $page) => $page
                ->has('auth.canCreateBlogCategories')
                ->where('auth.canCreateBlogCategories', false)
        );
    });

    it('shows posts menu to all authenticated users', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        // Посты должны быть доступны всем (проверяем что auth.user есть)
        $response->assertInertia(
            fn(Assert $page) => $page
                ->has('auth.user')
                ->whereNot('auth.user', null)
        );
    });
});