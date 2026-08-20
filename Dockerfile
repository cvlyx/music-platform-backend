FROM php:8.3-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    libzip-dev libicu-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Copy application code
COPY . .

# Generate key, cache config and routes
RUN php artisan key:generate --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan storage:link --force

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
