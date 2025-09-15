<?php

namespace App\Services;

use App\Models\HhAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class HhApi
{
    public function __construct(private readonly HhAccount $account) {}

    public function ensureFreshToken(bool $force = false): bool
    {
        $needRefresh = $force;

        if (!$this->account->access_token) {
            $needRefresh = true;
        }

        // Обновляем за минуту до истечения — буфер на сетевые лаги/часы
        if ($this->account->expires_at && now()->gte($this->account->expires_at->subMinute())) {
            $needRefresh = true;
        }

        if (!$needRefresh) {
            return true; // всё ок, токен живой
        }

        if (!$this->account->refresh_token) {
            return false; // нечем обновлять
        }

        $resp = Http::asForm()
            ->withHeaders([
                'User-Agent' => config('services.hh.user_agent'),
                'Accept'     => 'application/json',
            ])->post('https://hh.ru/oauth/token', [
                'grant_type'    => 'refresh_token',
                'client_id'     => config('services.hh.client_id'),
                'client_secret' => config('services.hh.client_secret'),
                'refresh_token' => $this->account->refresh_token,
            ]);

        if (!$resp->ok()) {
            return false;
        }

        $json = $resp->json();
        $this->account->update([
            'access_token'  => $json['access_token'] ?? null,
            'refresh_token' => $json['refresh_token'] ?? $this->account->refresh_token,
            'expires_at'    => isset($json['expires_in'])
                ? Carbon::now()->addSeconds((int) $json['expires_in'])
                : null,
            'token_payload' => $json,
        ]);

        return (bool) $this->account->access_token;
    }

    // По желанию: обёртка с единичным ретраем на 401/403
    public function get(string $path, array $query = [])
    {
        $r = $this->client()->get($this->url($path), $query);
        if (in_array($r->status(), [401, 403], true) && $this->ensureFreshToken(true)) {
            $r = $this->client()->get($this->url($path), $query);
        }
        return $r;
    }

    protected function client()
    {
        return Http::withToken($this->account->access_token ?? '')
            ->withHeaders([
                'User-Agent' => config('services.hh.user_agent'),
                'Accept'     => 'application/json',
            ]);
    }

    protected function url(string $path): string
    {
        return 'https://api.hh.ru/' . ltrim($path, '/');
    }
}
