FROM php:8.2-apache

RUN a2enmod rewrite headers

RUN apt-get update && apt-get install -y \
    git unzip \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    && docker-php-ext-install \
    zip \
    pdo \
    pdo_mysql \
    dom

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
