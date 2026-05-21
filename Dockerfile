FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libonig-dev \
    libpq-dev \
    libxml2-dev \
    nodejs \
    npm \
    && docker-php-ext-install dom mbstring pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

ENV APP_ENV=production
ENV APP_DEBUG=false

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN npm install && npm run build

EXPOSE 10000

CMD php artisan optimize:clear && php artisan view:clear && php artisan config:clear && php artisan route:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000