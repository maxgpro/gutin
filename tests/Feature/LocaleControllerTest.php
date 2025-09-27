<?php

use Illuminate\Support\Facades\Session;

test('it switches locale with valid locale', function () {
    $response = $this->post('/locale/switch', [
        'locale' => 'ru'
    ]);

    $response->assertRedirect();
    expect(Session::get('locale'))->toBe('ru');
});

// In Inertia-only mode we expect redirects rather than JSON responses
test('it redirects on locale switch requests', function () {
    $response = $this->post('/locale/switch', [
        'locale' => 'en'
    ]);

    $response->assertRedirect();
    expect(Session::get('locale'))->toBe('en');
});

test('it validates locale parameter', function () {
    $response = $this->post('/locale/switch', [
        'locale' => 'invalid'
    ]);

    $response->assertSessionHasErrors(['locale']);
});

test('it requires locale parameter', function () {
    $response = $this->post('/locale/switch', []);

    $response->assertSessionHasErrors(['locale']);
});

test('it throttles requests', function () {
    // Make 10 requests (within limit) using regular POST (Inertia-like behavior)
    for ($i = 0; $i < 10; $i++) {
        $response = $this->post('/locale/switch', ['locale' => 'en']);
        $response->assertRedirect();
    }

    // 11th request should be throttled — middleware returns JSON 429
    $response = $this->postJson('/locale/switch', ['locale' => 'ru']);
    $response->assertStatus(429);
    $response->assertJsonStructure(['message', 'retry_after']);
});

test('it accepts all configured locales', function () {
    $availableLocales = array_keys(config('app.available_locales'));

    foreach ($availableLocales as $locale) {
        $response = $this->post('/locale/switch', [
            'locale' => $locale
        ]);

        $response->assertRedirect();
        expect(Session::get('locale'))->toBe($locale);
    }
});
