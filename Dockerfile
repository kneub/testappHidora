FROM php:7.1-apache

MAINTAINER kneub <lkneub@gmail.com>
LABEL Description="This image is a php7.1 with apache and additionals modules"

# PHP modules
RUN apt-get update && apt-get install -y \
        wget \
        apt-transport-https \
        lsb-release \
        ca-certificates \
        libmcrypt-dev \
        exif \
        git \
        zip \
        unzip \
        libpq-dev \
        curl \
        nano


RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql

RUN docker-php-ext-install pdo pdo_pgsql bcmath

RUN a2enmod rewrite

# Composer install and build
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
COPY ./composer.json /var/www/html/

# Source copy
COPY ./web/ /var/www/html/web/
COPY ./app/ /var/www/html/app/

WORKDIR /var/www/html/
RUN composer install

# Virtual Host
COPY ./configs/vh/qsweb.conf /etc/apache2/sites-available/000-default.conf

# PHP ini
COPY ./configs/php/ /usr/local/etc/php/conf.d/
