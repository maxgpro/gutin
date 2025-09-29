<?php

namespace Database\Factories;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BlogPost::class;

    /**
     * Generate a placeholder image URL using placehold.co
     */
    private function generatePlaceholderImage(): string
    {
        $colors = ['3b82f6', '10b981', 'f59e0b', 'ef4444', '8b5cf6', '06b6d4', 'f97316', 'ec4899'];
        $topics = ['Tech', 'Code', 'Web', 'Dev', 'Blog', 'News', 'IT', 'Laravel'];

        $color = fake()->randomElement($colors);
        $topic = fake()->randomElement($topics);

        return "https://placehold.co/800x400/{$color}/ffffff/png?text=" . urlencode($topic);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $publishedAt = fake()->boolean(75) ? fake()->dateTimeBetween('-1 year', 'now') : null;
        $status = $publishedAt ? 'published' : fake()->randomElement(['draft', 'published']);

        // Generate multilingual titles
        $titles = [
            // Для русской локали используем локализованный провайдер текста
            'ru' => fake('ru_RU')->realTextBetween(20, 60),
            'en' => fake('en_US')->sentence(rand(3, 8)),
            'fr' => fake('fr_FR')->sentence(rand(3, 8)),
        ];

        // Generate multilingual content
        $content = [];
        foreach (['ru', 'en', 'fr'] as $locale) {
            $paragraphs = [];
            $paragraphCount = fake()->numberBetween(3, 8);

            for ($i = 0; $i < $paragraphCount; $i++) {
                if ($locale === 'ru') {
                    // Локализованный русский текст абзацами
                    $paragraphs[] = '<p>' . fake('ru_RU')->realTextBetween(280, 560) . '</p>';
                } else {
                    $sentences = fake()->numberBetween(3, 7);
                    $paragraphs[] = '<p>' . fake($locale === 'fr' ? 'fr_FR' : 'en_US')->paragraph($sentences) . '</p>';
                }
            }

            // Add some code blocks occasionally
            if (fake()->boolean(30)) {
                $codeBlock = '<pre><code class="language-php">' .
                    fake()->randomElement([
                        '<?php\n\nfunction example() {\n    return "Hello World!";\n}',
                        '<?php\n\nclass Example {\n    public function method() {\n        // Some logic here\n        return $this->data;\n    }\n}',
                        'npm install laravel-mix\nnpm run dev',
                    ]) . '</code></pre>';

                // Insert code block at random position
                $insertAt = fake()->numberBetween(1, max(1, count($paragraphs) - 1));
                array_splice($paragraphs, $insertAt, 0, $codeBlock);
            }

            $content[$locale] = implode("\n\n", $paragraphs);
        }

        return [
            'user_id' => User::factory(),
            'blog_category_id' => BlogCategory::factory(),
            'title' => $titles,
            'excerpt' => [
                'ru' => fake('ru_RU')->realTextBetween(160, 260),
                'en' => fake('en_US')->paragraph(3),
                'fr' => fake('fr_FR')->paragraph(3),
            ],
            'content' => $content,
            'featured_image' => fake()->boolean(60) ? $this->generatePlaceholderImage() : null,
            'meta_data' => [
                'meta_title' => $titles,
                'meta_description' => [
                    'ru' => fake('ru_RU')->realTextBetween(100, 160),
                    'en' => fake('en_US')->sentence(15),
                    'fr' => fake('fr_FR')->sentence(15),
                ],
                'keywords' => fake()->words(5),
                'author_bio' => fake()->sentence(),
            ],
            'status' => $status,
            'published_at' => $publishedAt,
            'views_count' => $status === 'published' ? fake()->numberBetween(0, 5000) : 0,
        ];
    }

    /**
     * Indicate that the blog post is published.
     */
    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'views_count' => fake()->numberBetween(10, 5000),
        ]);
    }

    /**
     * Indicate that the blog post is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
            'views_count' => 0,
        ]);
    }

    /**
     * Indicate that the blog post is popular (high view count).
     */
    public function popular(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-6 months', '-1 week'),
            'views_count' => fake()->numberBetween(1000, 10000),
        ]);
    }

    /**
     * Create a post with specific category.
     */
    public function forCategory(BlogCategory $category): static
    {
        return $this->state(fn(array $attributes) => [
            'blog_category_id' => $category->id,
        ]);
    }

    /**
     * Create a post by specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a recent post.
     */
    public function recent(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'views_count' => fake()->numberBetween(5, 500),
        ]);
    }

    /**
     * Create a post with no featured image.
     */
    public function withoutImage(): static
    {
        return $this->state(fn(array $attributes) => [
            'featured_image' => null,
        ]);
    }

    /**
     * Create a post with featured image.
     */
    public function withImage(): static
    {
        return $this->state(fn(array $attributes) => [
            'featured_image' => $this->generatePlaceholderImage(),
        ]);
    }
}
