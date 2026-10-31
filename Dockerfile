FROM php:8.3-apache

# Render routes web traffic to port 10000 by default.
ENV PORT=10000

RUN sed -i 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf \
    && a2enmod headers

WORKDIR /var/www/html
COPY . /var/www/html/

EXPOSE 10000
