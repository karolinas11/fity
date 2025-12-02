# ---------- base PHP stage ----------
FROM php:8.2-fpm

# Install system dependencies + nginx
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    supervisor \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required for Laravel
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache

# Copy Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# Copy Laravel source
COPY . /var/www/html
RUN ls -la
# Install dependencies
RUN composer install --no-dev --no-interaction --prefer-dist

# Set proper permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html/storage /var/www/html/bootstrap/cache

# ---------- NGINX CONFIG ----------
RUN rm /etc/nginx/sites-enabled/default
COPY ./docker/nginx.conf /etc/nginx/conf.d/default.conf

# ---------- SUPERVISOR CONFIG ----------
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Ensure PHP-FPM listens on 127.0.0.1 for nginx
RUN sed -i 's|^listen = .*|listen = 127.0.0.1:9000|' /usr/local/etc/php-fpm.d/www.conf

# ---------- ENTRYPOINT ----------
EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
