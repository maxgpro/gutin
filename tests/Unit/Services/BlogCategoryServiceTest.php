<?php

namespace Tests\Unit\Services;

use App\Models\BlogCategory;
use App\Services\BlogCategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogCategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BlogCategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BlogCategoryService();
    }

    public function test_get_active_categories_returns_only_active_categories(): void
    {
        $activeCategory = BlogCategory::factory()->active()->create();
        $inactiveCategory = BlogCategory::factory()->create(['is_active' => false]);

        $result = $this->service->getActiveCategories();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($activeCategory));
        $this->assertFalse($result->contains($inactiveCategory));
    }
}
