<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create blog_categories with JSONB fields (PostgreSQL)
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->jsonb('title');
            $table->jsonb('slug');
            $table->jsonb('description')->nullable();
            $table->string('color', 7)->default('#3B82F6');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('is_active');
        });

        // Create blog_posts with JSONB fields (PostgreSQL)
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_category_id')->constrained()->cascadeOnDelete();
            $table->jsonb('title');
            $table->jsonb('slug');
            $table->jsonb('excerpt')->nullable();
            $table->jsonb('content');
            $table->jsonb('meta_data')->nullable();
            $table->string('featured_image')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            // Common listing filters
            $table->index(['status', 'published_at']);
            $table->index('user_id');
            $table->index(['blog_category_id', 'status', 'published_at']);
        });

        // Functional B-Tree indexes for localized slugs (equality/joins)
        DB::statement("CREATE INDEX blog_categories_slug_ru ON blog_categories ((slug->>'ru'));");
        DB::statement("CREATE INDEX blog_categories_slug_en ON blog_categories ((slug->>'en'));");
        DB::statement("CREATE INDEX blog_categories_slug_fr ON blog_categories ((slug->>'fr'));");
        DB::statement("CREATE INDEX blog_posts_slug_ru ON blog_posts ((slug->>'ru'));");
        DB::statement("CREATE INDEX blog_posts_slug_en ON blog_posts ((slug->>'en'));");
        DB::statement("CREATE INDEX blog_posts_slug_fr ON blog_posts ((slug->>'fr'));");

        // Optional: indexes to speed up ORDER BY localized title
        DB::statement("CREATE INDEX blog_categories_title_ru ON blog_categories ((title->>'ru'));");
        DB::statement("CREATE INDEX blog_categories_title_en ON blog_categories ((title->>'en'));
");
        DB::statement("CREATE INDEX blog_categories_title_fr ON blog_categories ((title->>'fr'));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
    }
};
