# Laravel + Vue 3 + Inertia.js Project

Полнофункциональное веб-приложение с интеграцией HeadHunter API, системой ролей и блогом.

## Технологии

- **Backend**: Laravel 12
- **Frontend**: Vue 3 + TypeScript + Inertia.js
- **UI**: Reka UI (Radix Vue) + Tailwind CSS v4
- **База данных**: SQLite (по умолчанию)
- **Тестирование**: Pest PHP
- **Type-safe routing**: Laravel Wayfinder

## Быстрый старт

### 1. Клонирование и установка зависимостей

```bash
git clone <repository-url>
cd gutin.loc
composer install
```

### 2. Настройка окружения

```bash
# Скопировать .env.example в .env (происходит автоматически при composer install)
cp .env.example .env

# Или использовать готовый скрипт setup
composer run setup
```

### 3. Настройка переменных окружения

Отредактируйте `.env` файл:

```bash
# Основные настройки
APP_NAME="Your App Name"
APP_URL=http://your-domain.com

# HeadHunter API (обязательно для продакшена)
HH_CLIENT_ID=your_client_id
HH_CLIENT_SECRET=your_client_secret
HH_REDIRECT_URI=https://your-domain.com/hh/oauth/callback
HH_APP_USER_AGENT="your-app/1.0 (your-email@example.com)"
```

### 4. Запуск в разработке

```bash
# Разработка с hot reload
composer run dev

# Или с SSR
composer run dev:ssr
```

### 5. Тестирование

```bash
# Запуск всех тестов
composer run test

# Или напрямую
php artisan test
```

## Развертывание в продакшене

### 1. Установка на сервере

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 2. Настройка .env для продакшена

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Настройки базы данных
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Обязательные настройки HH API
HH_CLIENT_ID=your_production_client_id
HH_CLIENT_SECRET=your_production_client_secret
HH_REDIRECT_URI=https://your-domain.com/hh/oauth/callback
```

### 3. Миграции и данные

```bash
php artisan migrate --force
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder  # только для тестовых данных
```

## Система ролей

Приложение использует роли:

- **ADMIN** - полный доступ к управлению блогом и HH API
- **MENTEE** - доступ к HH API
- **USER** - базовый доступ

### Создание администратора

```bash
# Через сидер (только для разработки)
php artisan db:seed --class=UserSeeder

# Или вручную через tinker
php artisan tinker
$user = App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password')]);
$adminRole = App\Models\Role::where('name', 'ADMIN')->first();
$user->roles()->attach($adminRole);
```

## API интеграции

### HeadHunter API

Для работы с HH API необходимо:

1. Зарегистрировать приложение на https://dev.hh.ru/
2. Получить CLIENT_ID и CLIENT_SECRET
3. Настроить REDIRECT_URI в соответствии с вашим доменом

## Структура проекта

```
app/
├── Http/Middleware/
│   ├── AdminMiddleware.php      # Защита админских роутов
│   └── HhAccessMiddleware.php   # Защита HH роутов
├── Models/
│   ├── User.php                 # Пользователь с ролями
│   ├── Role.php                 # Роли пользователей
│   └── HhAccount.php           # Аккаунты HH с токенами
├── Services/
│   └── HhApi.php               # Сервис для работы с HH API
└── Policies/                   # Политики доступа

resources/js/
├── components/ui/              # UI компоненты (Reka UI)
├── pages/                      # Страницы Inertia.js
└── wayfinder/                  # Автогенерируемые маршруты

routes/
├── web.php                     # Основные маршруты
├── blog.php                    # Маршруты блога
├── hh.php                      # Маршруты HH интеграции
└── auth.php                    # Маршруты аутентификации
```

## Команды разработчика

```bash
# Разработка
composer run dev              # Запуск dev сервера с hot reload
composer run dev:ssr          # Запуск с SSR

# Сборка
npm run build                 # Production сборка
npm run build:ssr             # Сборка с SSR

# Тестирование  
composer run test             # Запуск тестов
php artisan test --filter=Blog # Тесты только блога

# База данных
php artisan migrate:fresh --seed  # Пересоздание БД с данными
php artisan db:seed --class=RoleSeeder  # Только роли
```

## Лицензия

MIT License