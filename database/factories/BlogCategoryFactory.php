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
            'ru' => [
                'Технологии', 'Веб-разработка', 'Мобильная разработка', 'Дизайн', 'DevOps',
                'Искусственный интеллект', 'Базы данных', 'Безопасность', 'Тестирование',
                'Управление проектами', 'Карьера в IT', 'Стартапы',
            ],
            'en' => [
                'Technology', 'Web Development', 'Mobile Development', 'Design', 'DevOps',
                'Artificial Intelligence', 'Databases', 'Security', 'Testing',
                'Project Management', 'IT Career', 'Startups',
            ],
            'fr' => [
                'Technologie', 'Développement Web', 'Développement Mobile', 'Design', 'DevOps',
                'Intelligence Artificielle', 'Bases de Données', 'Sécurité', 'Tests',
                'Gestion de Projet', 'Carrière IT', 'Startups',
            ],
        ];

        $colors = [
            '#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6',
            '#EC4899', '#06B6D4', '#84CC16', '#F97316', '#6B7280',
        ];

        $index = fake()->unique()->numberBetween(0, count($categories['ru']) - 1);

        return [
            'name' => [
                'ru' => $categories['ru'][$index],
                'en' => $categories['en'][$index],
                'fr' => $categories['fr'][$index],
            ],
            'description' => [
                'ru' => fake('ru_RU')->sentence(10, 20),
                'en' => fake('en_US')->sentence(10, 20),
                'fr' => fake('fr_FR')->sentence(10, 20),
            ],
            'color' => fake()->randomElement($colors),
            'is_active' => fake()->boolean(85),
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
