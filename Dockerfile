FROM ghcr.io/biodiversity-cz/php-fpm-noroot-socket:main@sha256:62a884d4d0705e01a30cd081a051da6c89a07c94b2e14d4622a5f99e004202d2

MAINTAINER Petr Novotný <krkabol@gmail.com>

COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp
