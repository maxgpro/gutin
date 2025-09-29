<?php

namespace Tests\Unit\Services;

use App\Http\Requests\Blog\BlogPostIndexRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\BlogPostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class BlogPostServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BlogPostService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BlogPostService();
    }

    public function test_get_filtered_posts_applies_status_filter()
    {
        // Create admin user and authenticate
        $adminRole = \App\Models\Role::create(['name' => 'admin']);
        $adminUser = User::factory()->create();
        $adminUser->roles()->attach($adminRole);
        $this->actingAs($adminUser);
        
        $category = BlogCategory::factory()->active()->create();
        
        BlogPost::factory()->published()->create(['user_id' => $adminUser->id, 'blog_category_id' => $category->id]);
        BlogPost::factory()->draft()->create(['user_id' => $adminUser->id, 'blog_category_id' => $category->id]);

        // Create request with status filter
        $request = new BlogPostIndexRequest();
        $request->merge(['status' => BlogPost::STATUS_DRAFT]);

        $result = $this->service->getFilteredPosts($request);

        $this->assertCount(1, $result->items());
        $this->assertEquals(BlogPost::STATUS_DRAFT, $result->items()[0]->status);
    }

    public function test_non_admin_users_see_only_published_posts()
    {
        // Create regular user and authenticate
        $regularUser = User::factory()->create();
        $this->actingAs($regularUser);
        
        $category = BlogCategory::factory()->active()->create();
        
        BlogPost::factory()->published()->create(['user_id' => $regularUser->id, 'blog_category_id' => $category->id]);
        BlogPost::factory()->draft()->create(['user_id' => $regularUser->id, 'blog_category_id' => $category->id]);
        BlogPost::factory()->create([
            'user_id' => $regularUser->id, 
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_ARCHIVED
        ]);

        // Regular user should only see published posts
        $request = new BlogPostIndexRequest();
        $result = $this->service->getFilteredPosts($request);

        $this->assertCount(1, $result->items());
        $this->assertEquals(BlogPost::STATUS_PUBLISHED, $result->items()[0]->status);
    }

    public function test_get_filtered_posts_applies_search()
    {
        // Clear any existing posts and logout any user
        BlogPost::query()->delete();
        Auth::logout();
        
        $user = User::factory()->create();
        $category = BlogCategory::factory()->active()->create();
        
        BlogPost::factory()->published()->create([
            'title' => 'Unique Laravel Search Test Tutorial',
            'user_id' => $user->id,
            'blog_category_id' => $category->id
        ]);
        BlogPost::factory()->published()->create([
            'title' => 'Vue.js Guide Without Target Word',
            'user_id' => $user->id,
            'blog_category_id' => $category->id
        ]);

        $request = new BlogPostIndexRequest();
        $request->merge(['search' => 'Unique Laravel Search Test']);

        $result = $this->service->getFilteredPosts($request);

        $this->assertCount(1, $result->items());
        $this->assertStringContainsString('Unique Laravel Search Test', $result->items()[0]->title);
    }

    public function test_get_related_posts_returns_posts_from_same_category()
    {
        $user = User::factory()->create();
        $category1 = BlogCategory::factory()->active()->create();
        $category2 = BlogCategory::factory()->active()->create();
        
        $mainPost = BlogPost::factory()->published()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category1->id
        ]);
        
        $relatedPost = BlogPost::factory()->published()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category1->id
        ]);
        
        $unrelatedPost = BlogPost::factory()->published()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category2->id
        ]);

        $result = $this->service->getRelatedPosts($mainPost);

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($relatedPost));
        $this->assertFalse($result->contains($unrelatedPost));
        $this->assertFalse($result->contains($mainPost)); // Should not include the main post itself
    }

    public function test_create_post_sets_published_at_for_published_posts()
    {
        $user = User::factory()->create();
        $category = BlogCategory::factory()->active()->create();
        
        $data = [
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'status' => BlogPost::STATUS_PUBLISHED,
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
        ];

        $post = $this->service->createPost($data);

        $this->assertNotNull($post->published_at);
        $this->assertEquals(BlogPost::STATUS_PUBLISHED, $post->status);
    }

    public function test_update_post_sets_published_at_when_publishing()
    {
        $user = User::factory()->create();
        $category = BlogCategory::factory()->active()->create();
        
        $post = BlogPost::factory()->draft()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id
        ]);

        $data = [
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
            'status' => BlogPost::STATUS_PUBLISHED,
            'blog_category_id' => $category->id,
        ];

        $updatedPost = $this->service->updatePost($post, $data);

        $this->assertNotNull($updatedPost->published_at);
        $this->assertEquals(BlogPost::STATUS_PUBLISHED, $updatedPost->status);
    }

    public function test_increment_views_only_for_published_posts()
    {
        $user = User::factory()->create();
        $category = BlogCategory::factory()->active()->create();
        
        $publishedPost = BlogPost::factory()->published()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'views_count' => 0
        ]);
        
        $draftPost = BlogPost::factory()->draft()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'views_count' => 0
        ]);

        $this->service->incrementViews($publishedPost);
        $this->service->incrementViews($draftPost);

        $publishedPost->refresh();
        $draftPost->refresh();

        $this->assertEquals(1, $publishedPost->views_count);
        $this->assertEquals(0, $draftPost->views_count);
    }
}