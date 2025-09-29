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
        // Since we're implementing translations from scratch, we'll drop and recreate tables
        // In production, you should backup and migrate existing data
        
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        
        // Recreate blog_categories with JSON fields
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('slug');
            $table->json('description')->nullable();
            $table->string('color', 7)->default('#3B82F6');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Recreate blog_posts with JSON fields
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_category_id')->constrained()->cascadeOnDelete();
            $table->json('title');
            $table->json('slug');
            $table->json('excerpt')->nullable();
            $table->json('content');
            $table->string('featured_image')->nullable();
            $table->json('meta_data')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
        
        // Create indexes for JSON fields performance (using specific locale paths)
        DB::statement("CREATE INDEX blog_categories_slug_ru ON blog_categories ((slug->>'ru'));");
        DB::statement("CREATE INDEX blog_categories_slug_en ON blog_categories ((slug->>'en'));");
        DB::statement("CREATE INDEX blog_posts_slug_ru ON blog_posts ((slug->>'ru'));");
        DB::statement("CREATE INDEX blog_posts_slug_en ON blog_posts ((slug->>'en'));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        
        // Restore original tables structure
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#3B82F6');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blog_category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->json('meta_data')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index('slug');
        });
    }
};
