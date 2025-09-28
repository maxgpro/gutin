# Laravel + Vue 3 + Inertia.js Project

Полнофункциональное веб-приложение с интеграцией HeadHunter API, системой ролей и блогом.

## Технологии

- **Backend**: Laravel 12
- **Frontend**: Vue 3 + TypeScript + Inertia.js
- **UI**: Reka UI (Radix Vue) + Tailwind CSS v4
- **База данных**: PostgreSQL 18
- **Тестирование**: Pest PHP (152 теста)
- **Type-safe routing**: Laravel Wayfinder

## Быстрый старт

### 1. Клонирование и установка зависимостей

```bash
git clone <repository-url>
cd gutin.loc
composer install
```

### 2. Установка и настройка PostgreSQL

```bash
# Установка PostgreSQL 18 (Ubuntu/Debian)
sudo apt update
sudo apt install postgresql-18 postgresql-client-18 postgresql-contrib-18

# Создание пользователя и баз данных
sudo -u postgres psql -p 5435 -c "CREATE USER gutin_user WITH ENCRYPTED PASSWORD 'password';"
sudo -u postgres psql -p 5435 -c "CREATE DATABASE gutin_blog OWNER gutin_user;"
sudo -u postgres psql -p 5435 -c "CREATE DATABASE gutin_blog_test OWNER gutin_user;"
sudo -u postgres psql -p 5435 -c "GRANT ALL PRIVILEGES ON DATABASE gutin_blog TO gutin_user;"
sudo -u postgres psql -p 5435 -c "GRANT ALL PRIVILEGES ON DATABASE gutin_blog_test TO gutin_user;"
```

### 3. Настройка окружения

Проект содержит несколько шаблонов конфигурации для разных сред:

```bash
# Для локальной разработки
cp .env.example .env

# Для продакшена (альтернативно)
cp .env.production.example .env

# Для тестирования (используется автоматически в phpunit.xml)
cp .env.testing.example .env.testing
```

### 4. Настройка переменных окружения

Отредактируйте `.env` файл:

```bash
# Основные настройки
APP_NAME="Your App Name"
APP_URL=http://your-domain.com

# База данных PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5435
DB_DATABASE=gutin_blog
DB_USERNAME=gutin_user
DB_PASSWORD=password

### 5. Инициализация базы данных

```bash
# Генерация ключа приложения
php artisan key:generate

# Запуск миграций
php artisan migrate

# Заполнение тестовыми данными
php artisan db:seed
```

### 6. Запуск в разработке

```bash
# Разработка с hot reload
composer run dev

# Или с SSR
composer run dev:ssr

# Только Laravel сервер
php artisan serve
```

### 7. Тестирование

```bash
# Запуск всех тестов (152 теста)
php artisan test

# Тесты с подробным выводом
php artisan test -v

# Тесты только блога
php artisan test --filter=Blog
```

## Развертывание в продакшене

### 1. Установка на сервере

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 2. Настройка PostgreSQL для продакшена

```bash
# Установка PostgreSQL на сервере
sudo apt install postgresql-18 postgresql-client-18

# Создание пользователя и базы данных
sudo -u postgres createuser --pwprompt gutin_user
sudo -u postgres createdb --owner=gutin_user gutin_blog
```

### 3. Настройка .env для продакшена

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# База данных PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=gutin_blog
DB_USERNAME=gutin_user
DB_PASSWORD=your_secure_password

### 4. Миграции и данные

```bash
# Запуск миграций в продакшене
php artisan migrate --force

# Создание ролей (обязательно)
php artisan db:seed --class=RoleSeeder

# Создание тестовых данных (опционально)
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=BlogSeeder
```

## Система ролей

Приложение использует роли:

- **ADMIN** - полный доступ к управлению блогом
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

## Структура проекта

```
# Конфигурационные файлы
.env.example                    # Шаблон для локальной разработки
.env.production.example         # Шаблон для продакшена
.env.testing.example           # Шаблон для тестирования

app/
├── Http/Middleware/
│   ├── AdminMiddleware.php      # Защита админских роутов
├── Models/
│   ├── User.php                 # Пользователь с ролями
│   ├── Role.php                 # Роли пользователей
├── Services/
└── Policies/                   # Политики доступа

resources/js/
├── components/ui/              # UI компоненты (Reka UI)
├── pages/                      # Страницы Inertia.js
└── wayfinder/                  # Автогенерируемые маршруты

routes/
├── web.php                     # Основные маршруты
├── blog.php                    # Маршруты блога
└── auth.php                    # Маршруты аутентификации

tests/
├── Feature/                    # Интеграционные тесты (143 теста)
├── Unit/                      # Модульные тесты (9 тестов)
└── Pest.php                   # Конфигурация Pest PHP
```

## Команды разработчика

```bash
# Разработка
composer run dev              # Запуск dev сервера с hot reload
composer run dev:ssr          # Запуск с SSR
php artisan serve            # Только Laravel сервер

# Сборка
npm run build                 # Production сборка
npm run build:ssr             # Сборка с SSR
npm run lint                  # Проверка кода ESLint + Prettier

# Тестирование  
php artisan test              # Запуск всех 152 тестов
php artisan test -v           # С подробным выводом
php artisan test --filter=Blog # Тесты только блога

# База данных
php artisan migrate:fresh --seed  # Пересоздание БД с данными
php artisan db:seed --class=RoleSeeder  # Только роли
php artisan db:seed --class=BlogSeeder  # Тестовые посты и категории

# PostgreSQL утилиты
pg_lsclusters                 # Список кластеров PostgreSQL
sudo -u postgres psql -p 5435 gutin_blog  # Подключение к БД
```

## Особенности PostgreSQL

Приложение использует PostgreSQL 18 с расширенными возможностями:

- **JSON поля** для мета-данных постов
- **Полнотекстовый поиск** по содержимому блога
- **Оптимизированные индексы** для быстрой фильтрации
- **Каскадное удаление** для связанных данных
- **Транзакционная целостность** всех операций

### Подключение к базе данных

```bash
# Локальная разработка (порт 5435)
sudo -u postgres psql -p 5435 gutin_blog

# Продакшен (стандартный порт 5432)
psql -h localhost -U gutin_user -d gutin_blog
```

## Тестирование

Проект включает комплексную систему тестирования с **152 тестами**:

### Покрытие тестами

- ✅ **Аутентификация** - login, logout, регистрация, сброс пароля
- ✅ **Авторизация** - роли, политики доступа, middleware
- ✅ **Блог система** - CRUD операции, фильтрация, пагинация
- ✅ **HeadHunter API** - OAuth, токены, middleware доступа
- ✅ **UI компоненты** - видимость элементов по ролям
- ✅ **Бизнес-логика** - счетчик просмотров, публикация постов

### Типы тестов

```bash
# Unit тесты (9 тестов)
php artisan test --testsuite=Unit

# Feature тесты (143 теста)  
php artisan test --testsuite=Feature

# Тесты по категориям
php artisan test --filter=Auth          # Аутентификация
php artisan test --filter=Blog          # Блог система
php artisan test --filter=Role          # Система ролей
```

### Тестовая база данных

Тесты используют отдельную PostgreSQL базу `gutin_blog_test` с автоматическим:
- Созданием транзакций перед каждым тестом
- Откатом изменений после теста
- Изоляцией тестовых данных

## Архитектура

### Паттерны проектирования

- **Repository Pattern** - через Eloquent модели
- **Service Layer** - `BlogPostService`
- **Policy Pattern** - авторизация через Laravel Policies
- **Middleware Pattern** - защита маршрутов
- **Observer Pattern** - автогенерация slug'ов

### Структура безопасности

- **CSRF защита** на всех формах
- **Rate limiting** для аутентификации
- **Role-based access** для всех админских функций
- **SQL injection защита** через Eloquent ORM

## Лицензия

MIT License