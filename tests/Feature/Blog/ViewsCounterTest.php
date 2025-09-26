<?php

namespace Tests\Feature\Blog;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewsCounterTest extends TestCase
{
    use RefreshDatabase;
    public function test_published_post_view_increments_counter(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = BlogCategory::factory()->create();
        $post = BlogPost::factory()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'views_count' => 5,
        ]);

        // Act
        $response = $this->get(route('blog.posts.show', $post));

        // Assert
        $response->assertOk();
        $this->assertEquals(6, $post->fresh()->views_count);
    }

    public function test_draft_post_view_does_not_increment_counter(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = BlogCategory::factory()->create();
        $post = BlogPost::factory()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_DRAFT,
            'published_at' => null,
            'views_count' => 5,
        ]);

        // Act - пытаемся посмотреть драфт как автор
        $response = $this->actingAs($user)->get(route('blog.posts.show', $post));

        // Assert
        $response->assertOk();
        $this->assertEquals(5, $post->fresh()->views_count); // Счётчик не изменился
    }

    public function test_multiple_views_increment_counter(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = BlogCategory::factory()->create();
        $post = BlogPost::factory()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'views_count' => 0,
        ]);

        // Act - несколько просмотров
        for ($i = 0; $i < 3; $i++) {
            $this->get(route('blog.posts.show', $post));
        }

        // Assert
        $this->assertEquals(3, $post->fresh()->views_count);
    }

    public function test_views_counter_displayed_on_post_list(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = BlogCategory::factory()->create();
        $post = BlogPost::factory()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'views_count' => 42,
        ]);

        // Act
        $response = $this->get(route('blog.posts.index'));

        // Assert
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('posts.data.0.views_count')
                ->where('posts.data.0.views_count', 42)
        );
    }

    public function test_views_counter_displayed_on_post_show(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = BlogCategory::factory()->create();
        $post = BlogPost::factory()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'views_count' => 123,
        ]);

        // Act
        $response = $this->get(route('blog.posts.show', $post));

        // Assert
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('post.views_count')
                ->where('post.views_count', 124) // +1 за текущий просмотр
        );
    }
}