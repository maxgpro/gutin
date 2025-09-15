<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\HhAccount;
use App\Services\HhApi;

class EnsureHhToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            // Считаем, что перед этим стоит стандартный 'auth' middleware.
            return redirect()->route('login');
        }

        /** @var HhAccount|null $account */
        $account = $user->hhAccounts()->first();
        if (!$account) {
            // Нет привязки HH — отправляем подключить
            return redirect()
                ->route('hh.redirect')
                ->with('warning', 'Подключите аккаунт hh.ru, чтобы выполнить это действие.');
        }

        $api = new HhApi($account);

        // 1) Пробуем освежить «на всякий случай» (с буфером в 1 минуту)
        $ok = $api->ensureFreshToken();

        // 2) Если не вышло (например, нет refresh_token или он невалиден) — просим переподключиться
        if (!$ok) {
            // Можно также удалить просроченные токены, чтобы не мешали
            // $account->update(['access_token' => null, 'expires_at' => null]);
            return redirect()
                ->route('hh.redirect')
                ->with('warning', 'Срок действия доступа hh.ru истёк. Подключите аккаунт заново.');
        }

        return $next($request);
    }
}
