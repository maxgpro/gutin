<?php

namespace Tests\Unit\Models;

use App\Models\BlogCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_active_returns_only_active_categories(): void
    {
        $active = BlogCategory::factory()->active()->create();
        $inactive = BlogCategory::factory()->inactive()->create();

        $result = BlogCategory::active()->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($active));
        $this->assertFalse($result->contains($inactive));
    }
}
