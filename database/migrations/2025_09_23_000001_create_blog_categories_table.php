<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->jsonb('title')->after('id');
            $table->jsonb('slug')->after('title');
            $table->jsonb('description')->nullable()->after('slug');
            $table->string('color', 7)->default('#3B82F6'); // Hex color for UI
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        // Create GIN index for JSON fields using raw SQL (PostgreSQL compatible)
        DB::statement('CREATE INDEX blog_categories_slug_gin ON blog_categories USING GIN (slug jsonb_path_ops);');

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_category_id')->constrained()->cascadeOnDelete();
            $table->jsonb('title')->after('blog_category_id');
            $table->jsonb('slug')->after('title');
            $table->jsonb('excerpt')->nullable()->after('slug');
            $table->jsonb('content')->after('excerpt');
            $table->string('featured_image')->nullable();
            $table->json('meta_data')->nullable(); // SEO meta, tags, etc.
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index('slug');
        });
        // Create GIN index for JSON fields using raw SQL (PostgreSQL compatible)
        DB::statement('CREATE INDEX blog_posts_slug_gin ON blog_posts USING GIN (slug jsonb_path_ops);');
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
    }
};