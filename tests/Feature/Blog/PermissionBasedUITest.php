<?php

declare(strict_types=1);

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

describe('Permission Based UI', function () {
    it('shows create button to admin on categories index', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('blog.categories.index'))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Blog/Categories/Index')
                    ->has('canCreate')
                    ->where('canCreate', true)
            );
    });

    it('hides create button from regular user on categories index', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('blog.categories.index'))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Blog/Categories/Index')
                    ->has('canCreate')
                    ->where('canCreate', false)
            );
    });

    it('hides create button from guest on categories index', function () {
        $this->get(route('blog.categories.index'))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Blog/Categories/Index')
                    ->has('canCreate')
                    ->where('canCreate', false)
            );
    });

    it('shows create button to admin on posts index', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('blog.posts.index'))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Blog/Posts/Index')
                    ->has('canCreate')
                    ->where('canCreate', true)
            );
    });

    it('hides create button from regular user on posts index', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('blog.posts.index'))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Blog/Posts/Index')
                    ->has('canCreate')
                    ->where('canCreate', false)
            );
    });

    it('hides create button from guest on posts index', function () {
        $this->get(route('blog.posts.index'))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Blog/Posts/Index')
                    ->has('canCreate')
                    ->where('canCreate', false)
            );
    });
});