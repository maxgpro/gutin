<?php

namespace Database\Factories;

use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlogCategory>
 */
class BlogCategoryFactory extends Factory
{
    protected $model = BlogCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Технологии',
            'Веб-разработка',
            'Мобильная разработка',
            'Дизайн',
            'DevOps',
            'Искусственный интеллект',
            'Базы данных',
            'Безопасность',
            'Тестирование',
            'Управление проектами',
            'Карьера в IT',
            'Стартапы',
        ];

        $colors = [
            '#3B82F6', // Blue
            '#EF4444', // Red
            '#10B981', // Green
            '#F59E0B', // Yellow
            '#8B5CF6', // Purple
            '#EC4899', // Pink
            '#06B6D4', // Cyan
            '#84CC16', // Lime
            '#F97316', // Orange
            '#6B7280', // Gray
        ];

        $name = fake()->unique()->randomElement($categories);

        return [
            'name' => $name,
            'slug' => null, // Will be auto-generated from name
            'description' => fake()->sentence(10, 20),
            'color' => fake()->randomElement($colors),
            'is_active' => fake()->boolean(85), // 85% chance of being active
        ];
    }

    /**
     * Indicate that the category is active.
     */
    public function active(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Set a specific color for the category.
     */
    public function color(string $color): static
    {
        return $this->state(fn(array $attributes) => [
            'color' => $color,
        ]);
    }
}
