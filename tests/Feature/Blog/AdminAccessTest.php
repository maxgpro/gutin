<?php

namespace Tests\Feature\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_by_status(): void
    {
        // Create admin role and user
        $adminRole = Role::create(['name' => Role::ADMIN]);
        $adminUser = User::factory()->create();
        $adminUser->roles()->attach($adminRole);

        // Create test data
        $category = BlogCategory::factory()->active()->create();
        BlogPost::factory()->published()->create(['user_id' => $adminUser->id, 'blog_category_id' => $category->id]);
        BlogPost::factory()->draft()->create(['user_id' => $adminUser->id, 'blog_category_id' => $category->id]);

        // Test admin access
        $response = $this->actingAs($adminUser)->get(route('blog.posts.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('canFilterByStatus')
                ->where('canFilterByStatus', true)
        );
    }

    public function test_regular_user_cannot_filter_by_status(): void
    {
        // Create regular user (no admin role)
        $regularUser = User::factory()->create();

        // Create test data
        $category = BlogCategory::factory()->active()->create();
        BlogPost::factory()->published()->create(['user_id' => $regularUser->id, 'blog_category_id' => $category->id]);

        // Test regular user access
        $response = $this->actingAs($regularUser)->get(route('blog.posts.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('canFilterByStatus')
                ->where('canFilterByStatus', false)
        );
    }

    public function test_guest_cannot_filter_by_status(): void
    {
        // Create test data
        $user = User::factory()->create();
        $category = BlogCategory::factory()->active()->create();
        BlogPost::factory()->published()->create(['user_id' => $user->id, 'blog_category_id' => $category->id]);

        // Test guest access
        $response = $this->get(route('blog.posts.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('canFilterByStatus')
                ->where('canFilterByStatus', false)
        );
    }

    public function test_admin_sees_all_posts_without_filter(): void
    {
        // Create admin role and user
        $adminRole = Role::create(['name' => Role::ADMIN]);
        $adminUser = User::factory()->create();
        $adminUser->roles()->attach($adminRole);

        // Create test data with different statuses
        $category = BlogCategory::factory()->active()->create();
        BlogPost::factory()->published()->create(['user_id' => $adminUser->id, 'blog_category_id' => $category->id]);
        BlogPost::factory()->draft()->create(['user_id' => $adminUser->id, 'blog_category_id' => $category->id]);

        // Test admin sees all posts
        $response = $this->actingAs($adminUser)->get(route('blog.posts.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('posts.data')
                ->whereContains('posts.data', fn ($post) => $post['status'] === BlogPost::STATUS_PUBLISHED)
                ->whereContains('posts.data', fn ($post) => $post['status'] === BlogPost::STATUS_DRAFT)
        );
    }

    public function test_regular_user_sees_only_published_posts(): void
    {
        // Create regular user and admin for creating draft
        $regularUser = User::factory()->create();
        $adminRole = Role::create(['name' => Role::ADMIN]);
        $adminUser = User::factory()->create();
        $adminUser->roles()->attach($adminRole);

        // Create test data with different statuses
        $category = BlogCategory::factory()->active()->create();
        BlogPost::factory()->published()->create(['user_id' => $adminUser->id, 'blog_category_id' => $category->id]);
        BlogPost::factory()->draft()->create(['user_id' => $adminUser->id, 'blog_category_id' => $category->id]);

        // Test regular user sees only published posts
        $response = $this->actingAs($regularUser)->get(route('blog.posts.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('posts.data')
                ->where('posts.total', 1) // Only one published post
                ->where('posts.data.0.status', BlogPost::STATUS_PUBLISHED)
        );
    }
}