<?php

use App\Models\BlogCategory;

test('category creates slugs with ID prefix', function () {
    $category = BlogCategory::factory()->create([
        'title' => [
            'en' => 'Web Development',
            'ru' => 'Веб-разработка',
            'fr' => 'Développement web'
        ]
    ]);

    expect($category->getLocalizedSlug('en'))->toBe($category->id . '-web-development');
    expect($category->getLocalizedSlug('ru'))->toStartWith($category->id . '-');
    expect($category->getLocalizedSlug('fr'))->toStartWith($category->id . '-');
    
    // Check that all slugs contain the ID prefix
    expect($category->getLocalizedSlug('en'))->toContain('-web-development');
    expect($category->getLocalizedSlug('ru'))->toMatch('/^' . $category->id . '-.+$/');
    expect($category->getLocalizedSlug('fr'))->toMatch('/^' . $category->id . '-.+$/');
});

test('categories with same name get different ID prefixes', function () {
    $category1 = BlogCategory::factory()->create([
        'title' => [
            'en' => 'Programming',
            'ru' => 'Программирование'
        ]
    ]);

    $category2 = BlogCategory::factory()->create([
        'title' => [
            'en' => 'Programming', // Same name
            'ru' => 'Другое программирование'
        ]
    ]);

    // With ID prefixes, both can have same base slug since IDs are unique
    expect($category1->getLocalizedSlug('en'))->toBe($category1->id . '-programming');
    expect($category2->getLocalizedSlug('en'))->toBe($category2->id . '-programming');
    expect($category1->id)->not->toBe($category2->id);
});

test('category can be found by ID-prefixed slug', function () {
    $category = BlogCategory::factory()->create([
        'title' => [
            'en' => 'Test Category',
            'ru' => 'Тестовая категория'
        ]
    ]);

    $model = new BlogCategory();
    $found = $model->resolveRouteBinding($category->id . '-test-category');

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($category->id);
});

test('category numeric ID still works for backward compatibility', function () {
    $category = BlogCategory::factory()->create([
        'title' => [
            'en' => 'Test Category'
        ]
    ]);

    $model = new BlogCategory();
    $found = $model->resolveRouteBinding((string) $category->id);

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($category->id);
});

test('category slug updates when title changes', function () {
    $category = BlogCategory::factory()->create([
        'title' => [
            'en' => 'Original Name'
        ]
    ]);

    $originalSlug = $category->getLocalizedSlug('en');
    expect($originalSlug)->toBe($category->id . '-original-name');

    // Update title
    $category->update([
        'title' => [
            'en' => 'Updated Name'
        ]
    ]);

    $category->refresh();
    $newSlug = $category->getLocalizedSlug('en');
    expect($newSlug)->toBe($category->id . '-updated-name');
});