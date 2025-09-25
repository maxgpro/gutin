<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => Role::ADMIN,
                'description' => 'Администратор с полными правами доступа',
            ],
            [
                'name' => Role::MENTEE,
                'description' => 'Менти с доступом к HeadHunter интеграции',
            ],
            [
                'name' => Role::USER,
                'description' => 'Обычный зарегистрированный пользователь',
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
