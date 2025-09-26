<?php

namespace Tests\Feature\Blog;

use App\Http\Requests\Blog\BlogPostIndexRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class BlogPostRequestAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_filter_by_draft_status(): void
    {
        // Arrange
        $request = new BlogPostIndexRequest();
        $request->merge(['status' => BlogPost::STATUS_DRAFT]);
        
        // Act & Assert
        $this->assertFalse($request->authorize());
    }

    public function test_guest_can_filter_by_published_status(): void
    {
        // Arrange
        $request = new BlogPostIndexRequest();
        $request->merge(['status' => BlogPost::STATUS_PUBLISHED]);
        
        // Act & Assert
        $this->assertTrue($request->authorize());
    }

    public function test_guest_can_access_without_status_filter(): void
    {
        // Arrange
        $request = new BlogPostIndexRequest();
        
        // Act & Assert
        $this->assertTrue($request->authorize());
    }

    public function test_regular_user_cannot_filter_by_draft_status(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $request = new BlogPostIndexRequest();
        $request->merge(['status' => BlogPost::STATUS_DRAFT]);
        
        // Act & Assert
        $this->assertFalse($request->authorize());
    }

    public function test_regular_user_can_filter_by_published_status(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $request = new BlogPostIndexRequest();
        $request->merge(['status' => BlogPost::STATUS_PUBLISHED]);
        
        // Act & Assert
        $this->assertTrue($request->authorize());
    }

    public function test_admin_can_filter_by_any_status(): void
    {
        // Arrange
        $adminRole = Role::create(['name' => Role::ADMIN]);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);
        $this->actingAs($admin);
        
        $statuses = [BlogPost::STATUS_DRAFT, BlogPost::STATUS_PUBLISHED, BlogPost::STATUS_ARCHIVED];
        
        foreach ($statuses as $status) {
            $request = new BlogPostIndexRequest();
            $request->merge(['status' => $status]);
            
            // Act & Assert
            $this->assertTrue($request->authorize(), "Admin should be able to filter by status: {$status}");
        }
    }

    public function test_request_blocks_invalid_status_filter_in_route(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = BlogCategory::factory()->active()->create();
        BlogPost::factory()->published()->create(['user_id' => $user->id, 'blog_category_id' => $category->id]);
        
        // Act - try to access with draft filter as regular user
        $response = $this->actingAs($user)->get(route('blog.posts.index', ['status' => BlogPost::STATUS_DRAFT]));
        
        // Assert - should get 403 Forbidden
        $response->assertStatus(403);
    }

    public function test_admin_can_access_draft_filter_in_route(): void
    {
        // Arrange
        $adminRole = Role::create(['name' => Role::ADMIN]);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);
        
        $category = BlogCategory::factory()->active()->create();
        BlogPost::factory()->draft()->create(['user_id' => $admin->id, 'blog_category_id' => $category->id]);
        
        // Act
        $response = $this->actingAs($admin)->get(route('blog.posts.index', ['status' => BlogPost::STATUS_DRAFT]));
        
        // Assert
        $response->assertOk();
    }
}