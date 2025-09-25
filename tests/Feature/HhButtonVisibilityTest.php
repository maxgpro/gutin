<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

describe('HH Button Visibility', function () {
    it('shows HH button to admin on dashboard', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Dashboard')
                    ->has('auth.canAccessHh')
                    ->where('auth.canAccessHh', true)
            );
    });

    it('shows HH button to mentee on dashboard', function () {
        $this->seed(\Database\Seeders\RoleSeeder::class);
        
        $mentee = User::factory()->mentee()->create();

        $this->actingAs($mentee)
            ->get(route('dashboard'))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Dashboard')
                    ->has('auth.canAccessHh')
                    ->where('auth.canAccessHh', true)
            );
    });

    it('hides HH button from regular user on dashboard', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('Dashboard')
                    ->has('auth.canAccessHh')
                    ->where('auth.canAccessHh', false)
            );
    });

    it('hides HH button from guest', function () {
        // Guest не может получить доступ к dashboard, будет редирект на login
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    });
});