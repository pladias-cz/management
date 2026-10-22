FROM ghcr.io/biodiversity-cz/php-fpm-noroot-socket:main

MAINTAINER Petr Novotný <krkabol@gmail.com>

COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp
