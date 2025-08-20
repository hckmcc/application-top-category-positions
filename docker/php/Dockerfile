FROM php:8.3-fpm

RUN apt update -q && \
    apt install -q -y libpq-dev && \
    docker-php-ext-install pdo_pgsql pgsql

RUN apt update -q && \
    apt install -q -y curl gnupg && \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt install -q -y nodejs && \
    npm install -g npm@latest
