<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

describe('Sidebar Menu Visibility', function () {
    it('shows HH menu item to admin', function () {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        // Проверяем что canAccessHh передается в props
        $response->assertInertia(
            fn(Assert $page) => $page
                ->has('auth.canAccessHh')
                ->where('auth.canAccessHh', true)
        );
    });

    it('shows HH menu item to mentee', function () {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        $mentee = User::factory()->mentee()->create();

        $response = $this->actingAs($mentee)->get(route('dashboard'));

        $response->assertInertia(
            fn(Assert $page) => $page
                ->has('auth.canAccessHh')
                ->where('auth.canAccessHh', true)
        );
    });

    it('hides HH menu item from regular user', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn(Assert $page) => $page
                ->has('auth.canAccessHh')
                ->where('auth.canAccessHh', false)
        );
    });

    it('hides HH menu item from guest by redirecting to login', function () {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    });
});