#!/bin/bash
set -e

echo "=== Добавляем репозитории ==="
sudo apt install -y wget ca-certificates
wget -qO- https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo tee /etc/apt/trusted.gpg.d/postgres.asc
echo "deb http://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" | sudo tee /etc/apt/sources.list.d/pgdg.list

echo "=== Обновление системы ==="
sudo apt update && sudo apt upgrade -y

echo "=== Установка PHP 8.4 и расширений ==="
sudo apt install -y software-properties-common
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.4 php8.4-fpm php8.4-cli php8.4-pgsql php8.4-mbstring \
    php8.4-xml php8.4-curl php8.4-zip php8.4-bcmath php8.4-gd php8.4-intl \
    php8.4-redis php8.4-opcache php8.4-readline

echo "=== Установка PostgreSQL 18 ==="
sudo apt install -y postgresql-18 postgresql-client-18

echo "=== Установка Nginx ==="
sudo apt install -y nginx

echo "=== Установка Node.js 24 LTS ==="
curl -fsSL https://deb.nodesource.com/setup_24.x | sudo -E bash -
sudo apt install -y nodejs

echo "=== Установка Composer ==="
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

echo "=== Установка Git и unzip ==="
sudo apt install -y git unzip

echo "=== Установка Supervisor ==="
sudo apt install -y supervisor

echo "=== Настройка firewall ==="
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

echo "=== Готово! ==="
