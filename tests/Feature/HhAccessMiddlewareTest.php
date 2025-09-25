<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;

describe('HH Access Middleware', function () {
    it('allows admin to access HH routes', function () {
        $admin = User::factory()->admin()->create();
        
        $this->actingAs($admin)
            ->get(route('hh.redirect'))
            ->assertStatus(302); // redirect to HH
    });

    it('allows mentee to access HH routes', function () {
        $mentee = User::factory()->mentee()->create();
        
        $this->actingAs($mentee)
            ->get(route('hh.redirect'))
            ->assertStatus(302); // redirect to HH
    });

    it('denies regular user access to HH routes', function () {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->get(route('hh.redirect'))
            ->assertStatus(403);
    });

    it('redirects guest to login', function () {
        $this->get(route('hh.redirect'))
            ->assertRedirect(route('login'));
    });

    it('allows callback access without authentication', function () {
        // Callback должен быть доступен без аутентификации
        // 400 ошибка нормальна, так как мы не передаем параметры от HH
        $this->get(route('hh.callback'))
            ->assertStatus(400);
    });
});