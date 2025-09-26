<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create blog categories
        $categories = BlogCategory::factory()
            ->count(8)
            ->active()
            ->create();

        // Get the admin user or create one if not exists
        $adminUser = User::where('email', 'admin@example.com')->first();
        if (!$adminUser) {
            $adminUser = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
        }

        // Create additional authors
        $authors = User::factory()->count(3)->create();
        $allUsers = collect([$adminUser])->merge($authors);

        // Create published blog posts
        foreach ($categories as $category) {
            // Create 3-7 published posts per category
            $postsCount = fake()->numberBetween(3, 7);

            for ($i = 0; $i < $postsCount; $i++) {
                $author = $allUsers->random();

                BlogPost::factory()
                    ->published()
                    ->forCategory($category)
                    ->byUser($author)
                    ->create();
            }

            // Create 1-2 draft posts per category
            $draftsCount = fake()->numberBetween(1, 2);

            for ($i = 0; $i < $draftsCount; $i++) {
                $author = $allUsers->random();

                BlogPost::factory()
                    ->draft()
                    ->forCategory($category)
                    ->byUser($author)
                    ->create();
            }
        }

        // Create some popular posts
        BlogPost::factory()
            ->count(5)
            ->popular()
            ->create([
                'blog_category_id' => $categories->random()->id,
                'user_id' => $allUsers->random()->id,
            ]);

        // Create some recent posts
        BlogPost::factory()
            ->count(10)
            ->recent()
            ->create([
                'blog_category_id' => $categories->random()->id,
                'user_id' => $allUsers->random()->id,
            ]);

        // Create some archived posts
        BlogPost::factory()
            ->count(5)
            ->state([
                'status' => BlogPost::STATUS_ARCHIVED,
                'published_at' => fake()->dateTimeBetween('-6 months', '-1 month'),
            ])
            ->create([
                'blog_category_id' => $categories->random()->id,
                'user_id' => $allUsers->random()->id,
            ]);

        $this->command->info('Blog seeder completed successfully!');
        $this->command->info('Created:');
        $this->command->info('- ' . BlogCategory::count() . ' blog categories');
        $this->command->info('- ' . BlogPost::count() . ' blog posts');
        $this->command->info('- ' . BlogPost::where('status', BlogPost::STATUS_PUBLISHED)->count() . ' published posts');
        $this->command->info('- ' . BlogPost::where('status', BlogPost::STATUS_DRAFT)->count() . ' draft posts');
        $this->command->info('- ' . BlogPost::where('status', BlogPost::STATUS_ARCHIVED)->count() . ' archived posts');
    }
}
