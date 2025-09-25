<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (App::environment('production')) {
            $this->command->info('Skipping UserSeeder in production environment.');
            return;
        }

        // Создаем админа
        $admin = User::updateOrCreate(
            [
                'email' => 'admin@example.com'
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('pass'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        // Присваиваем роль админа
        $adminRole = \App\Models\Role::where('name', \App\Models\Role::ADMIN)->first();
        if ($adminRole && !$admin->hasRole(\App\Models\Role::ADMIN)) {
            $admin->roles()->attach($adminRole);
        }

        // Создаем mentee
        $mentee = User::updateOrCreate(  
            [
                'email' => 'mentee@example.com'
            ],
            [
                'name' => 'Mentee',
                'email' => 'mentee@example.com',
                'password' => bcrypt('pass'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        // Присваиваем роль mentee
        $menteeRole = \App\Models\Role::where('name', \App\Models\Role::MENTEE)->first();
        if ($menteeRole && !$mentee->hasRole(\App\Models\Role::MENTEE)) {
            $mentee->roles()->attach($menteeRole);
        }

        // Создаем обычного пользователя  
        $tester = User::updateOrCreate(
            [
                'email' => 'user@example.com'
            ],
            [
                'name' => 'User',
                'email' => 'user@example.com',
                'password' => bcrypt('pass'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        // Присваиваем роль обычного пользователя
        $userRole = \App\Models\Role::where('name', \App\Models\Role::USER)->first();
        if ($userRole && !$tester->hasRole(\App\Models\Role::USER)) {
            $tester->roles()->attach($userRole);
        }
    }
}