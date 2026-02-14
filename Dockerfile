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
# Install PHP Dependencies
# -------------------------
RUN composer install --no-dev --optimize-autoloader

# -------------------------
# Clear cache (ignore error if no .env yet)
# -------------------------
RUN php artisan config:clear || true
RUN php artisan route:clear || true
RUN php artisan view:clear || true

ENV PORT=8080
EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=$PORT
