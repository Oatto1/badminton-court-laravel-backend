FROM php:8.4-cli

# -------------------------
# System Dependencies
# -------------------------
RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    intl \
    && rm -rf /var/lib/apt/lists/*

# -------------------------
# Install Composer
# -------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# -------------------------
# Create Laravel Required Folders
# -------------------------
RUN mkdir -p \
    storage/framework/views \
    storage/framework/cache \
    storage/framework/sessions \
    bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# -------------------------
# Install PHP Dependencies
# -------------------------
RUN composer install --no-dev --optimize-autoloader

# -------------------------
# Production Environment
# -------------------------
ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 8080

# -------------------------
# Start Application
# -------------------------
CMD php artisan optimize:clear && \
    php artisan migrate --force && \
    php artisan db:seed --force && \
    php -S 0.0.0.0:$PORT -t public
