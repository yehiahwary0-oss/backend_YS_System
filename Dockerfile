# Stage 1: Dependencies
FROM php:8.4-fpm-alpine AS deps
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo_pgsql pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Stage 2: Production
FROM php:8.4-fpm-alpine AS runner
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_pgsql pdo_mysql zip \
    && addgroup -g 1001 -S appgroup && adduser -S -u 1001 -G appgroup appuser

WORKDIR /app
COPY --from=deps /app/vendor ./vendor
COPY . .
RUN composer dump-autoload --no-dev --optimize

RUN chown -R appuser:appgroup /app/storage /app/bootstrap/cache
USER appuser

EXPOSE 9000
CMD ["php-fpm"]
