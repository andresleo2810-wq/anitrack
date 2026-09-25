# ---- Stage 1: compila los assets de Vite ----
FROM node:20-alpine AS assets
WORKDIR /build
COPY package.json package-lock.json* ./
RUN npm install
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
RUN npm run build

# ---- Stage 2: app Laravel ----
FROM php:8.3-cli
RUN apt-get update && apt-get install -y --no-install-recommends \
      libpq-dev libzip-dev libonig-dev unzip git curl \
    && docker-php-ext-install pdo_pgsql zip bcmath \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
COPY --from=assets /build/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && php artisan package:discover --ansi \
    && php artisan optimize

EXPOSE 10000
CMD sh -c "php artisan migrate --force || php artisan migrate --force || true; php artisan serve --host=0.0.0.0 --port=${PORT}"