<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CheckHhAuth extends Command
{
    protected $signature = 'hh:check-auth';
    protected $description = 'Проверка конфигурации и доступности OAuth авторизации hh.ru';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $clientId     = config('services.hh.client_id');
        $clientSecret = config('services.hh.client_secret');
        $redirectUri  = config('services.hh.redirect');
        $userAgent    = config('services.hh.user_agent');

        // 1) Проверяем что параметры заданы
        if (!$clientId || !$clientSecret || !$redirectUri || !$userAgent) {
            $this->error('❌ Конфигурация hh не полная. Проверьте .env и config/services.php');
            return self::FAILURE;
        }

        $this->info("✅ Конфиг найден:");
        $this->line("- client_id: {$clientId}");
        $this->line("- redirect : {$redirectUri}");
        $this->line("- user_agent: {$userAgent}");

        // 2) Пробуем запрос к /oauth/token с заведомо неверным code
        $response = Http::asForm()
            ->withHeaders([
                'User-Agent' => $userAgent,
                'Accept'     => 'application/json',
            ])
            ->post('https://hh.ru/oauth/token', [
                'grant_type'    => 'authorization_code',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri'  => $redirectUri,
                'code'          => 'fake_code',
            ]);

        $body = $response->json();
        $error = $body['error'] ?? null;
        $desc  = mb_strtolower($body['error_description'] ?? '');

        $healthy =
            // классический «здоровый» исход
            ($error === 'invalid_grant')
            ||
            // альтернатива от HH: «код не найден» — значит, client/secret/redirect приняты
            ($error === 'invalid_request' && str_contains($desc, 'code not found'));

        if ($healthy) {
            $this->info('✅ Подключение к hh.ru работает, конфигурация валидна.');
            $this->line('Ответ: '.json_encode($body, JSON_UNESCAPED_UNICODE));
            self::SUCCESS;
        } else {
            $this->error('❌ Ошибка ответа от hh.ru (похоже на проблему конфигурации)');
            $this->line('Статус: '.$response->status());
            $this->line('Тело: '.$response->body());
            self::FAILURE;
        }

        // Проверка /me с заведомо неверным токеном — должны получить 401 или 403 с oauth/bad_authorization
        $me = Http::withToken('bad_token')
            ->withHeaders([
                'User-Agent' => $userAgent,
                'Accept'     => 'application/json',
            ])->get('https://api.hh.ru/me');

        $status = $me->status();
        $body   = $me->json();
        $errors = $body['errors'][0] ?? null;

        $authFailure =
            $status === 401 ||
            ($status === 403
            && isset($errors['type'], $errors['value'])
            && $errors['type'] === 'oauth'
            && $errors['value'] === 'bad_authorization');

        if ($authFailure) {
            $this->info('✅ /me отказал из-за авторизации (ожидаемо на плохой токен) — канал и заголовки ок.');
        } else {
            $this->warn('⚠️ /me вернул неожиданный ответ');
            $this->line('Статус: '.$status);
            $this->line('Тело: '.json_encode($body, JSON_UNESCAPED_UNICODE));
        }
    }
}
