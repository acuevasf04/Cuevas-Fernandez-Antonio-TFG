FROM php:8.2-fpm-alpine

RUN apk update && apk add --no-cache \
    $PHPIZE_DEPS

RUN docker-php-ext-install pdo pdo_mysql

RUN apk del $PHPIZE_DEPS