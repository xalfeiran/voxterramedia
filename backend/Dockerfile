FROM php:8.4-fpm

# System dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN mkdir -p bootstrap/cache storage/framework/{cache/data,sessions,testing,views} storage/logs storage/app/public \
    && chmod -R 775 bootstrap/cache storage \
    && composer install --no-interaction --optimize-autoloader --no-dev \
    && cp .env.example .env \
    && php artisan key:generate

EXPOSE 9000
CMD ["php-fpm"]
