<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;

describe('Role System', function () {
    it('creates roles through seeder', function () {
        $this->artisan('db:seed', ['--class' => 'RoleSeeder']);
        
        expect(Role::count())->toBe(3);
        expect(Role::where('name', Role::ADMIN)->exists())->toBeTrue();
        expect(Role::where('name', Role::MENTEE)->exists())->toBeTrue();
        expect(Role::where('name', Role::USER)->exists())->toBeTrue();
    });

    it('allows users to have roles', function () {
        $user = User::factory()->create();
        $menteeRole = Role::firstOrCreate(['name' => Role::MENTEE], ['description' => 'Менти']);
        
        $user->roles()->attach($menteeRole);
        
        expect($user->hasRole(Role::MENTEE))->toBeTrue();
        expect($user->hasRole(Role::ADMIN))->toBeFalse();
    });
});