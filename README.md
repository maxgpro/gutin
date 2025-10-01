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
APP_URL=http://mydomain.loc

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

Ниже — инструкции для VPS/выделенного сервера и для хостинга сайтов (shared/виртуальный хостинг с панелями).

> 💡 **Быстрый старт**: Для Ubuntu 24.04 используйте готовый скрипт `./srv_setup.bash` — он автоматически установит все необходимые пакеты.

### A. VPS/Выделенный сервер (Nginx + PHP-FPM)

0) Установка системных пакетов (Ubuntu 24.04)

**Автоматическая установка (рекомендуется):**

```bash
# Запуск готового скрипта установки
chmod +x srv_setup.bash
./srv_setup.bash
```

Скрипт `srv_setup.bash` автоматически установит:
- PHP 8.4 с необходимыми расширениями
- PostgreSQL 18
- Nginx  
- Node.js 24 LTS
- Composer
- Git, unzip
- Supervisor для очередей
- UFW firewall с правильными портами

**Ручная установка (альтернативно):**

```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# PHP 8.4 с расширениями для Laravel (актуальная версия октябрь 2025)
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.4 php8.4-fpm php8.4-cli php8.4-pgsql php8.4-mbstring \
    php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath php8.4-gd php8.4-intl \
    php8.4-redis php8.4-opcache php8.4-readline

# PostgreSQL 18 (актуальная версия октябрь 2025)
sudo apt install -y postgresql-18 postgresql-client-18

# Nginx
sudo apt install -y nginx

# Node.js 24 LTS (актуальная версия октябрь 2025)
curl -fsSL https://deb.nodesource.com/setup_24.x | sudo -E bash -
sudo apt install -y nodejs

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Git (если не установлен)
sudo apt install -y git unzip

# Supervisor (для очередей Laravel)
sudo apt install -y supervisor

# Firewall (базовая настройка)
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp  
sudo ufw allow 443/tcp
sudo ufw --force enable
```

1) Установка зависимостей проекта

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build:ssr
```

2) Настройка PostgreSQL

```bash
# Создание пользователя и базы данных
sudo -u postgres createuser --pwprompt gutin_user
sudo -u postgres createdb --owner=gutin_user gutin_blog

# Проверка подключения
sudo -u postgres psql -c "ALTER USER gutin_user CREATEDB;"
```

3) .env (production)

```bash
cp .env.production.example .env
```

```bash
APP_ENV=production
APP_DEBUG=false
APP_URL=https://mydomain.loc

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=gutin_blog
DB_USERNAME=gutin_user
DB_PASSWORD=your_secure_password

# Кэширование (для продакшена)
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database
```

4) Миграции и кэши

```bash
# Права доступа к директориям Laravel
sudo chown -R www-data:www-data /var/www/myapp/storage &&
sudo chown -R www-data:www-data /var/www/myapp/bootstrap/cache &&
sudo chmod -R 775 /var/www/myapp/storage &&
sudo chmod -R 775 /var/www/myapp/bootstrap/cache

# Laravel артефакты
php artisan key:generate --force &&
php artisan storage:link --force &&
php artisan migrate --force &&
php artisan config:cache &&
php artisan route:cache &&
php artisan view:cache &&
php artisan optimize
```

5) SSR-сервер Inertia (если SSR включён — см. `config/inertia.php`)

**Вариант A: С SSR (рекомендуется для VPS)**
```bash
# Сборка с SSR
npm run build:ssr

# Запуск SSR-сервера вручную (для тестирования)
php artisan inertia:start-ssr

# Systemd service для автозапуска SSR
sudo tee /etc/systemd/system/laravel-ssr.service > /dev/null <<EOF
[Unit]
Description=Laravel Inertia SSR
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/myapp
ExecStart=/usr/bin/php artisan inertia:start-ssr
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
EOF

sudo systemctl enable laravel-ssr
sudo systemctl start laravel-ssr
sudo systemctl status laravel-ssr
```

**Вариант B: Без SSR (упрощенный деплой)**
```bash
# Отключить SSR
echo "INERTIA_SSR_ENABLED=false" >> .env

# Обычная сборка
npm run build

# Очистить кэши
php artisan config:cache

# SSR процесс не нужен - только Nginx
```

6) Веб-сервер Nginx

```bash
# Создание конфига сайта (сначала только HTTP)
sudo tee /etc/nginx/sites-available/myapp > /dev/null <<EOF
server {
    listen 80;
    server_name mydomain.loc www.mydomain.loc;

    root /var/www/myapp/public;
    index index.php index.html;

    # Gzip сжатие
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml;

    # Кэширование статики
    location ~* \\\.(css|js|jpg|jpeg|png|gif|ico|svg|woff|woff2|ttf)\\\$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files \\\$uri =404;
    }

    location / {
        try_files \\\$uri \\\$uri/ /index.php?\\\$query_string;
    }

    location ~ \\\\.php\\\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \\\$realpath_root\\\$fastcgi_script_name;
        include fastcgi_params;
        
        # Таймауты для SSR
        fastcgi_read_timeout 300;
        fastcgi_connect_timeout 300;
        fastcgi_send_timeout 300;
    }

    # Безопасность - запрет доступа к критичным папкам
    location ~ /(\.env|vendor|storage|bootstrap|resources|config|database|tests)/ { 
        deny all; 
    }
    
    location ~ /\\\\.ht {
        deny all;
    }
}
EOF

# Активация сайта
sudo ln -s /etc/nginx/sites-available/myapp /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

7) SSL-сертификаты Let's Encrypt

```bash
# Установка Certbot
sudo apt install -y certbot python3-certbot-nginx

# Получение SSL-сертификата (замените mydomain.loc на ваш домен)
# Certbot автоматически модифицирует конфиг Nginx, добавив SSL и редирект
sudo certbot --nginx -d mydomain.loc -d www.mydomain.loc

# Автообновление сертификатов (проверка)
sudo certbot renew --dry-run

# Планировщик автообновления (добавляется автоматически при установке)
# Проверить можно командой:
sudo systemctl status certbot.timer
```

После успешного выполнения `certbot --nginx`, ваш конфиг автоматически будет обновлен:

```nginx
server {
    listen 80;
    server_name mydomain.loc www.mydomain.loc;
    return 301 https://mydomain.loc$request_uri; # Автоматический редирект на HTTPS
}

server {
    listen 443 ssl http2;
    server_name mydomain.loc www.mydomain.loc;
    
    # SSL сертификаты (добавлены автоматически)
    ssl_certificate /etc/letsencrypt/live/mydomain.loc/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/mydomain.loc/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;

    # Остальная конфигурация остается без изменений...
}
```

8) Очереди Laravel (с Supervisor)

```bash
# Создание таблицы очередей
php artisan queue:table && php artisan migrate --force

# Конфиг Supervisor для очередей
sudo tee /etc/supervisor/conf.d/laravel-worker.conf > /dev/null <<EOF
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/myapp/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/laravel-worker.log
stopwaitsecs=3600
EOF

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

8) Планировщик Laravel (Cron)

```
* * * * * /usr/bin/php /var/www/myapp/artisan schedule:run >> /dev/null 2>&1
```

### B. Хостинг сайтов (shared/виртуальный)

Если у вас панель (cPanel/Plesk/ISPmanager) и нет systemd, действуем так:

1) Сборка на локальной машине/CI

```bash
npm ci
npm run build:ssr
composer install --no-dev --optimize-autoloader
```

2) Загрузка артефактов на хостинг

Загрузите:
- `public/` (включая `public/build`)
- `vendor/`, `bootstrap/`, `resources/`
- `.env`

3) Document root

В панели укажите Document Root = `/path/to/project/public`.

4) Миграции и кэши

```bash
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

5) SSR на shared-хостинге

Если нет возможности держать фон-процесс SSR:

**Способ 1: Через .env переменную**
```bash
# Добавить в .env
echo "INERTIA_SSR_ENABLED=false" >> .env

# Обычная сборка без SSR
npm run build

# Очистить кэши
php artisan config:cache
```

**Способ 2: В config/inertia.php**
```php
'ssr' => [
    'enabled' => false, // Отключить SSR
    // ...
],
```

Приложение будет работать без SSR; включите позже на VPS.

## Устранение проблем SSR

### ReferenceError: document is not defined

Если SSR падает с ошибкой `document is not defined`, проблема в использовании браузерных API в серверной среде:

```bash
# Симптомы
ReferenceError: document is not defined
ReferenceError: localStorage is not defined
ReferenceError: window is not defined
```

**Решение**: Оберните код в проверки среды выполнения:

```typescript
// Неправильно
const locale = document.documentElement.lang;
localStorage.setItem('key', 'value');

// Правильно
const locale = typeof document !== 'undefined' 
    ? document.documentElement.lang 
    : 'en';

if (typeof localStorage !== 'undefined') {
    localStorage.setItem('key', 'value');
}
```

После исправления пересоберите SSR:
```bash
npm run build:ssr
php artisan inertia:start-ssr
```

### Чек-лист прод-готовности

- [ ] APP_ENV=production, APP_DEBUG=false
- [ ] APP_KEY задан
- [ ] DB подключение рабочее
- [ ] `npm run build:ssr` (или `npm run build` если SSR отключён)
- [ ] `php artisan migrate --force`
- [ ] Статика доступна из `public/build`
- [ ] SSR-процесс запущен (VPS) или SSR отключён (shared)
- [ ] SSL-сертификат получен (`sudo certbot --nginx`)
- [ ] HTTPS редирект работает
- [ ] Очереди/cron (по необходимости)
- [ ] Права на `storage/` и `bootstrap/cache/`
- [ ] Firewall настроен (UFW: 22, 80, 443)

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
srv_setup.bash                 # Скрипт автоустановки системных пакетов Ubuntu 24.04

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

# Системная установка
chmod +x srv_setup.bash       # Права на скрипт установки
./srv_setup.bash              # Автоустановка всех зависимостей Ubuntu 24.04

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