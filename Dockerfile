FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite

COPY . /var/www/html/

RUN a2enmod rewrite \
    && mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html/data

EXPOSE 80
