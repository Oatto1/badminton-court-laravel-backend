# -------------------------
# Base Image
# -------------------------
FROM php:8.4-cli

# -------------------------
# Install system dependencies
# -------------------------
RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libpq-dev \
    libpng-dev \
    libzip-dev \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    zip \
    && rm -rf /var/lib/apt/lists/*

# -------------------------
# Install Composer
# -------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -------------------------
# Set working directory
# -------------------------
WORKDIR /app

# -------------------------
# Copy project files
# -------------------------
COPY . .

# -------------------------
# Install PHP dependencies
# -------------------------
RUN composer install --no-dev --optimize-autoloader

# -------------------------
# Laravel optimization
# -------------------------
RUN php artisan config:clear || true
RUN php artisan route:clear || true
RUN php artisan view:clear || true

# -------------------------
# Expose Railway port
# -------------------------
ENV PORT=8080
EXPOSE 8080

# -------------------------
# Start Laravel
# -------------------------
CMD php artisan serve --host=0.0.0.0 --port=$PORT
