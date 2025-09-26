<?php

namespace Tests\Unit\Services;

use App\Http\Requests\Blog\BlogPostIndexRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use App\Services\BlogPostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
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

    public function test_get_active_categories_returns_only_active_categories()
    {
        // Create active and inactive categories
        $activeCategory = BlogCategory::factory()->active()->create();
        $inactiveCategory = BlogCategory::factory()->create(['is_active' => false]);

        $result = $this->service->getActiveCategories();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($activeCategory));
        $this->assertFalse($result->contains($inactiveCategory));
    }

    public function test_get_filtered_posts_applies_status_filter()
    {
        $user = User::factory()->create();
        $category = BlogCategory::factory()->active()->create();
        
        BlogPost::factory()->published()->create(['user_id' => $user->id, 'blog_category_id' => $category->id]);
        BlogPost::factory()->draft()->create(['user_id' => $user->id, 'blog_category_id' => $category->id]);

        // Create request with status filter
        $request = new BlogPostIndexRequest();
        $request->merge(['status' => BlogPost::STATUS_DRAFT]);

        $result = $this->service->getFilteredPosts($request);

        $this->assertCount(1, $result->items());
        $this->assertEquals(BlogPost::STATUS_DRAFT, $result->items()[0]->status);
    }

    public function test_get_filtered_posts_applies_search()
    {
        $user = User::factory()->create();
        $category = BlogCategory::factory()->active()->create();
        
        BlogPost::factory()->published()->create([
            'title' => 'Laravel Tutorial',
            'user_id' => $user->id,
            'blog_category_id' => $category->id
        ]);
        BlogPost::factory()->published()->create([
            'title' => 'Vue.js Guide',
            'user_id' => $user->id,
            'blog_category_id' => $category->id
        ]);

        $request = new BlogPostIndexRequest();
        $request->merge(['search' => 'Laravel']);

        $result = $this->service->getFilteredPosts($request);

        $this->assertCount(1, $result->items());
        $this->assertStringContainsString('Laravel', $result->items()[0]->title);
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