<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\BlogPostResource;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogPostResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reading_time_is_computed_from_localized_html_content()
    {
        app()->setLocale('en');

        $user = User::factory()->create();
        $category = BlogCategory::factory()->active()->create();

        // 450 слов => ceil(450 / 200) = 3 минуты чтения
        $words = implode(' ', array_fill(0, 450, 'word'));
        $html = '<p>' . $words . '</p>';

        $post = BlogPost::factory()->published()->create([
            'user_id' => $user->id,
            'blog_category_id' => $category->id,
            'content' => [
                'en' => $html,
            ],
        ]);

        $array = BlogPostResource::make($post->load(['user', 'category']))->toArray(request());

        $this->assertArrayHasKey('reading_time', $array);
        $this->assertSame(3, $array['reading_time']);
    }
}
