<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a user with admin role.
     */
    public function admin(): static
    {
        return $this->afterCreating(function ($user) {
            $adminRole = Role::firstOrCreate(
                ['name' => Role::ADMIN],
                ['description' => 'Администратор с полными правами']
            );
            if (!$user->roles->contains($adminRole)) {
                $user->roles()->attach($adminRole);
            }
        });
    }

    /**
     * Create a user with mentee role.
     */
    public function mentee(): static
    {
        return $this->afterCreating(function ($user) {
            $menteeRole = Role::firstOrCreate(
                ['name' => Role::MENTEE],
                ['description' => 'Менти с доступом к HeadHunter']
            );
            if (!$user->roles->contains($menteeRole)) {
                $user->roles()->attach($menteeRole);
            }
        });
    }

    /**
     * Create a user with user role.
     */
    public function user(): static
    {
        return $this->afterCreating(function ($user) {
            $userRole = Role::firstOrCreate(
                ['name' => Role::USER],
                ['description' => 'Обычный зарегистрированный пользователь']
            );
            if (!$user->roles->contains($userRole)) {
                $user->roles()->attach($userRole);
            }
        });
    }
}
