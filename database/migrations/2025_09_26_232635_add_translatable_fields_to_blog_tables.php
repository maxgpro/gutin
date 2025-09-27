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
        Schema::table('blog_categories', function (Blueprint $table) {
            // Drop existing non-JSON columns and constraints
            $table->dropUnique(['slug']);
            $table->dropColumn(['name', 'slug', 'description']);
            
            // Add JSON columns for translations
            $table->json('name')->after('id');
            $table->json('slug')->after('name');
            $table->json('description')->nullable()->after('slug');
        });
        
        // Create GIN index for JSON fields using raw SQL
        DB::statement('CREATE INDEX blog_categories_slug_gin ON blog_categories USING GIN (slug);');

        Schema::table('blog_posts', function (Blueprint $table) {
            // Drop existing non-JSON columns and constraints
            $table->dropUnique(['slug']);
            $table->dropColumn(['title', 'slug', 'excerpt', 'content']);
            
            // Add JSON columns for translations
            $table->json('title')->after('blog_category_id');
            $table->json('slug')->after('title');
            $table->json('excerpt')->nullable()->after('slug');
            $table->json('content')->after('excerpt');
        });
        
        // Create GIN index for JSON fields using raw SQL
        DB::statement('CREATE INDEX blog_posts_slug_gin ON blog_posts USING GIN (slug);');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop GIN indexes
        DB::statement('DROP INDEX IF EXISTS blog_posts_slug_gin;');
        DB::statement('DROP INDEX IF EXISTS blog_categories_slug_gin;');

        Schema::table('blog_posts', function (Blueprint $table) {
            // Drop JSON columns
            $table->dropColumn(['title', 'slug', 'excerpt', 'content']);
            
            // Restore original string columns
            $table->string('title')->after('blog_category_id');
            $table->string('slug')->unique()->after('title');
            $table->text('excerpt')->nullable()->after('slug');
            $table->longText('content')->after('excerpt');
        });

        Schema::table('blog_categories', function (Blueprint $table) {
            // Drop JSON columns
            $table->dropColumn(['name', 'slug', 'description']);
            
            // Restore original string columns
            $table->string('name')->after('id');
            $table->string('slug')->unique()->after('name');
            $table->text('description')->nullable()->after('slug');
        });
    }
};
