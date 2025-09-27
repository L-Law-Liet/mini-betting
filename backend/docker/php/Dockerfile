FROM php:8.4-fpm

# Устанавливаем зависимости
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl \
    && docker-php-ext-install pdo pdo_mysql zip bcmath

WORKDIR /var/www/html

# Копируем composer из официального образа
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Копируем только composer.* для кеша зависимостей
COPY composer.json composer.lock ./

# Устанавливаем зависимости без скриптов (artisan ещё нет)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Теперь копируем весь проект (artisan появится)
COPY . .

# Запускаем post-autoload-dump (artisan уже есть)
RUN composer run-script post-autoload-dump

EXPOSE 9000
