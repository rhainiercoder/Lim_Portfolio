FROM php:8.3-apache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Render routes web traffic to port 10000 by default.
ENV PORT=10000

RUN sed -i 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf \
    && a2enmod headers

WORKDIR /var/www/html
COPY composer.json ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader
COPY . /var/www/html/

EXPOSE 10000
