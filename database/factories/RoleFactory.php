<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];
    }

    public function admin(): static
    {
        return $this->state([
            'name' => Role::ADMIN,
            'description' => 'Администратор с полными правами',
        ]);
    }

    public function mentee(): static
    {
        return $this->state([
            'name' => Role::MENTEE,
            'description' => 'Менти с доступом к HeadHunter',
        ]);
    }

    public function user(): static
    {
        return $this->state([
            'name' => Role::USER,
            'description' => 'Обычный пользователь',
        ]);
    }
}
