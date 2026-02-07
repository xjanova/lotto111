FROM php:8.3-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    icu-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    linux-headers \
    supervisor \
    nginx

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        xml \
        opcache

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# ────────────────────────────────────────
# Development Stage
# ────────────────────────────────────────
FROM base AS development

# Install Xdebug for development
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

# PHP development config
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/development.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/development.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/development.ini

COPY . .

RUN composer install --no-interaction

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

# ────────────────────────────────────────
# Production Stage
# ────────────────────────────────────────
FROM base AS production

# PHP production config
COPY docker/php/php.ini /usr/local/etc/php/conf.d/production.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Nginx config
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Supervisor config
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Copy application
COPY --chown=www-data:www-data . .

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
