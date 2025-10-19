FROM php:8.2-fpm
RUN apt-get update && apt-get install -y \
git unzip libzip-dev libicu-dev libonig-dev mariadb-client \
&& docker-php-ext-install pdo pdo_mysql intl zip
# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
WORKDIR /app
RUN composer install --no-interaction --no-progress || true
