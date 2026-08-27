FROM php:8.2-fpm-alpine

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www

COPY . .

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8000