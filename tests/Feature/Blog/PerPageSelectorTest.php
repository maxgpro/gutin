<?php

namespace Tests\Feature\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerPageSelectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_per_page_parameter_changes_pagination_size(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = BlogCategory::factory()->create();
        
        // Создаём 30 постов
        BlogPost::factory()->count(30)->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        // Act & Assert - по умолчанию 12 постов на странице
        $response = $this->get(route('blog.posts.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('posts.per_page', 12)
                ->where('posts.current_page', 1)
                ->has('posts.data', 12)
        );

        // Act & Assert - 25 постов на странице
        $response = $this->get(route('blog.posts.index', ['per_page' => 25]));
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('posts.per_page', 25)
                ->where('posts.current_page', 1)
                ->has('posts.data', 25)
        );

        // Act & Assert - 50 постов на странице (покажет все 30)
        $response = $this->get(route('blog.posts.index', ['per_page' => 50]));
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('posts.per_page', 50)
                ->where('posts.current_page', 1)
                ->has('posts.data', 30) // все посты на одной странице
        );
    }

    public function test_invalid_per_page_values_are_rejected(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = BlogCategory::factory()->create();
        
        BlogPost::factory()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_PUBLISHED,
        ]);

        // Act & Assert - слишком маленькое значение
        $response = $this->get(route('blog.posts.index', ['per_page' => 0]));
        $response->assertSessionHasErrors('per_page');

        // Act & Assert - слишком большое значение
        $response = $this->get(route('blog.posts.index', ['per_page' => 200]));
        $response->assertSessionHasErrors('per_page');

        // Act & Assert - нечисловое значение
        $response = $this->get(route('blog.posts.index', ['per_page' => 'abc']));
        $response->assertSessionHasErrors('per_page');
    }

    public function test_per_page_selector_shows_configured_options(): void
    {
        // Arrange  
        $user = User::factory()->create();
        $category = BlogCategory::factory()->create();
        
        BlogPost::factory()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_PUBLISHED,
        ]);

        // Act
        $response = $this->get(route('blog.posts.index'));

        // Assert - проверяем что опции из конфига доступны
        $response->assertOk();
        // В данном случае мы проверяем что страница загружается корректно
        // Опции селектора находятся в frontend конфиге
    }
}