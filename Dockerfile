FROM ghcr.io/biodiversity-cz/php-fpm-noroot-socket:main@sha256:ff81e43a5f926ae7fe4b939e0890a77c3c06e1d9f3c9795f55267efeb8cf9059

MAINTAINER Petr Novotný <krkabol@gmail.com>

COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp
