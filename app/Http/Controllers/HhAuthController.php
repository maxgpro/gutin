<?php

namespace App\Http\Controllers;

use App\Models\HhAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class HhAuthController extends Controller
{
    public function redirect(Request $request)
    {
        // защита от CSRF/подмены редиректа
        $state = Str::random(40);
        $request->session()->put('hh_oauth_state', $state);

        $query = http_build_query([
            'response_type' => 'code',
            'client_id'     => config('services.hh.client_id'),
            'redirect_uri'  => config('services.hh.redirect'),
            'state'         => $state,
            // hh обычно не требует scope — доступ определяется типом вашего приложения
            // 'scope'      => '...', // оставляем пустым
        ]);

        return redirect()->away('https://hh.ru/oauth/authorize?'.$query);
    }

    public function callback(Request $request)
    {
        // 1) безопасность: сверяем state
        if (!$request->has('state') || $request->session()->pull('hh_oauth_state') !== $request->string('state')) {
            abort(400, 'Invalid OAuth state');
        }

        $code = $request->string('code');
        if ($code->isEmpty()) {
            abort(400, 'Missing authorization code');
        }

        // 2) меняем code на токен
        $tokenResp = Http::asForm()
            ->withHeaders([
                'User-Agent' => config('services.hh.user_agent'),
                'Accept'     => 'application/json',
            ])
            ->post('https://hh.ru/oauth/token', [
                'grant_type'    => 'authorization_code',
                'client_id'     => config('services.hh.client_id'),
                'client_secret' => config('services.hh.client_secret'),
                'redirect_uri'  => config('services.hh.redirect'),
                'code'          => $code,
            ]);

        if ($tokenResp->failed()) {
            // удобно посмотреть, что именно ответил hh
            return back()->with('error', 'OAuth token exchange failed: '.$tokenResp->body());
        }

        $token = $tokenResp->json();
        // ожидаем поля: access_token, token_type, refresh_token, expires_in
        $expiresAt = isset($token['expires_in'])
            ? Carbon::now()->addSeconds((int)$token['expires_in'])
            : null;

        // 3) узнаём кто авторизован (соискатель) через /me
        $me = Http::withToken($token['access_token'])
            ->withHeaders([
                'User-Agent' => config('services.hh.user_agent'),
                'Accept'     => 'application/json',
            ])->get('https://api.hh.ru/me');

        if ($me->failed()) {
            return back()->with('error', 'Failed to fetch profile: '.$me->body());
        }

        $profile = $me->json();
        $hhUserId = (string)($profile['id'] ?? '');

        // 4) Привязываем к локальному пользователю
        $user = Auth::user();
        if (!$user) {
            // Простой пример: если в профиле есть email — можно найти/создать локального пользователя.
            $email = $profile['email'] ?? null;
            $name  = $profile['first_name'] ?? 'HH User';
            $user  = \App\Models\User::firstOrCreate(
                ['email' => $email ?? Str::uuid().'@example.local'],
                ['name'  => $name]
            );
            Auth::login($user);
        }

        HhAccount::updateOrCreate(
            ['user_id' => $user->id, 'hh_user_id' => $hhUserId],
            [
                'token_type'    => $token['token_type'] ?? 'Bearer',
                'access_token'  => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? null,
                'expires_at'    => $expiresAt,
                'profile'       => $profile,
                'token_payload' => $token,
            ]
        );

        return redirect()->route('dashboard')->with('success', 'HH аккаунт подключён');
    }

    public function disconnect(Request $request)
    {
        $request->user()->hhAccounts()->delete();
        return back()->with('success', 'HH аккаунт отсоединён у этого пользователя');
    }
}
