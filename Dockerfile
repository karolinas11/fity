# ---------- builder: composer + php extensions ----------
FROM php:8.2-fpm AS builder

ARG UID=1000
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

WORKDIR /var/www/html

# system deps for PHP extensions and common tools
RUN apt-get update && apt-get install -y --no-install-recommends \
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
    zlib1g-dev \
    ca-certificates \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# configure & install PHP extensions
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install -j$(nproc) pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache

# Install composer (copy from official composer image)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# copy composer metadata, install dependencies (cacheable)
COPY composer.json composer.lock /var/www/html/

RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

# copy application source
COPY . /var/www/html

# run composer scripts (if you rely on them)
RUN composer dump-autoload --optimize

# fix permissions for builder stage (will be copied to final)
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html/bootstrap/cache /var/www/html/storage || true

# ---------- optional assets stage (Node) ----------
# This stage builds frontend assets (Laravel Mix / Vite). If you don't use node assets remove this stage.
FROM node:18-bullseye AS assets

WORKDIR /var/www/html

# copy package.json only to leverage caching
COPY package.json package-lock.json* /var/www/html/
RUN if [ -f package-lock.json ]; then npm ci --silent; else npm i --silent; fi

# copy all frontend sources (adjust path if you keep assets somewhere else)
COPY . /var/www/html

# run build - assume "npm run build" produces files into public/ or public/build
RUN npm run build --if-present

# ---------- final runtime image ----------
FROM php:8.2-fpm AS release

WORKDIR /var/www/html

# system libs needed at runtime
RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# copy PHP extensions built in builder (extensions are part of base image install, so skip)
# copy composer binary
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# copy app + vendor from builder
COPY --from=builder /var/www/html /var/www/html

# copy built assets from assets stage (if built)
COPY --from=assets /var/www/html/public /var/www/html/public

# set permissions (storage & cache)
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html/bootstrap/cache /var/www/html/storage || true

# Add a small entrypoint that sets permissions and can optionally run migrations
COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose php-fpm socket port (Coolify will proxy to this)
EXPOSE 9000

# recommended for php-fpm performance
ENV PHP_FPM_MAX_CHILDREN=10 \
    PHP_FPM_MAX_REQUESTS=500 \
    APP_ENV=production \
    APP_DEBUG=false

# default user is www-data for safety
USER www-data

# run entrypoint (which execs php-fpm)
ENTRYPOINT ["sh", "/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
