<?php

use Illuminate\Support\Facades\Session;

test('it switches locale with valid locale', function () {
    $response = $this->post('/locale/switch', [
        'locale' => 'ru'
    ]);

    $response->assertRedirect();
    expect(Session::get('locale'))->toBe('ru');
});

test('it returns json for ajax requests', function () {
    $response = $this->postJson('/locale/switch', [
        'locale' => 'en'
    ]);

    $response->assertJson([
        'success' => true,
        'locale' => 'en'
    ]);
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
    // Make 10 requests (within limit)
    for ($i = 0; $i < 10; $i++) {
        $response = $this->postJson('/locale/switch', ['locale' => 'en']);
        $response->assertSuccessful();
    }

    // 11th request should be throttled
    $response = $this->postJson('/locale/switch', ['locale' => 'ru']);
    $response->assertStatus(429);
    $response->assertJsonStructure(['message', 'retry_after']);
});

test('it accepts all configured locales', function () {
    $availableLocales = array_keys(config('app.available_locales'));

    foreach ($availableLocales as $locale) {
        $response = $this->postJson('/locale/switch', [
            'locale' => $locale
        ]);

        $response->assertJson([
            'success' => true,
            'locale' => $locale
        ]);
    }
});
