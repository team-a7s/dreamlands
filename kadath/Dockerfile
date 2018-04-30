FROM php:7.2-fpm-alpine3.7

RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.ustc.edu.cn/' /etc/apk/repositories
RUN apk add --no-cache --virtual .persistent-deps postgresql-dev gmp-dev autoconf make gcc musl-dev freetype-dev libpng-dev jpeg-dev
RUN pecl install apcu \
    && docker-php-ext-enable apcu
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql gmp bcmath gd

CMD ["php-fpm"]
