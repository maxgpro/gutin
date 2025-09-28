<?php

namespace Tests\Feature\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class PerPageSelectorTest extends TestCase
{
    use RefreshDatabase;

    public function test_per_page_parameter_changes_pagination_size(): void
    {
        // Arrange
        $defaultPerPage = Config::get('ui.posts_per_page', 6);
        $user = User::factory()->create();
        $category = BlogCategory::factory()->create();
        
        // Создаём 30 постов
        BlogPost::factory()->count(30)->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        // Act & Assert - по умолчанию posts_per_page постов на странице (из конфига)
        $response = $this->get(route('blog.posts.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('posts.per_page', $defaultPerPage)
                ->where('posts.current_page', 1)
                ->has('posts.data', $defaultPerPage)
        );

        // Act & Assert - 25 постов на странице
        $response = $this->get(route('blog.posts.index', ['per_page' => 12]));
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
        $defaultPerPage = Config::get('ui.posts_per_page', 6);
        $user = User::factory()->create();
        $category = BlogCategory::factory()->create();
        
        BlogPost::factory()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_PUBLISHED,
        ]);

        // Act
        $response = $this->get(route('blog.posts.index'));

        // Assert - проверяем что страница загружается с правильной конфигурацией
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('posts.per_page', $defaultPerPage)
        );
        
        // Опции селектора (6, 12, 25, 50, 100) находятся в frontend конфиге
        // и будут тестироваться в браузерных тестах
    }

    public function test_pagination_respects_config_changes(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = BlogCategory::factory()->create();
        
        // Создаём достаточно постов для тестирования
        BlogPost::factory()->count(20)->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        // Временно изменяем конфигурацию на 10 постов на страницу
        Config::set('ui.posts_per_page', 10);

        // Act & Assert
        $response = $this->get(route('blog.posts.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('posts.per_page', 10)
                ->where('posts.current_page', 1)
                ->has('posts.data', 10)
        );

        // Проверяем что остальные посты попали на вторую страницу
        $response = $this->get(route('blog.posts.index', ['page' => 2]));
        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->where('posts.per_page', 10)
                ->where('posts.current_page', 2)
                ->has('posts.data', 10)
        );
    }
}