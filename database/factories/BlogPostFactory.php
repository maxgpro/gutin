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
        $title = fake()->sentence(rand(3, 8));
        $publishedAt = fake()->boolean(75) ? fake()->dateTimeBetween('-1 year', 'now') : null;
        $status = $publishedAt ? 'published' : fake()->randomElement(['draft', 'published']);

        // Generate realistic content with multiple paragraphs
        $paragraphs = [];
        $paragraphCount = fake()->numberBetween(3, 8);

        for ($i = 0; $i < $paragraphCount; $i++) {
            $sentences = fake()->numberBetween(3, 7);
            $paragraphs[] = '<p>' . fake()->paragraph($sentences) . '</p>';
        }

        // Add some code blocks occasionally
        if (fake()->boolean(30)) {
            $codeBlock = '<pre><code class="language-php">' .
                fake()->randomElement([
                    '<?php

function example() {
    return "Hello World!";
}',
                    '<?php

class Example {
    public function method() {
        // Some logic here
        return $this->data;
    }
}',
                    'npm install laravel-mix
npm run dev',
                ]) . '</code></pre>';

            // Insert code block at random position
            $insertAt = fake()->numberBetween(1, count($paragraphs) - 1);
            array_splice($paragraphs, $insertAt, 0, $codeBlock);
        }

        $content = implode("\n\n", $paragraphs);

        return [
            'user_id' => User::factory(),
            'blog_category_id' => BlogCategory::factory(),
            'title' => $title,
            'slug' => null, // Will be auto-generated from title
            'excerpt' => fake()->paragraph(3),
            'content' => $content,
            'featured_image' => fake()->boolean(60) ? $this->generatePlaceholderImage() : null,
            'meta_data' => [
                'meta_title' => $title,
                'meta_description' => fake()->sentence(15),
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
