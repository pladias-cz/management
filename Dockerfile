FROM ghcr.io/biodiversity-cz/php-fpm-noroot-socket:main@sha256:a659a8dc7a12552227bada668c79b6d569b7b4036d174aa2fc79b1ddab5c2e40

MAINTAINER Petr Novotný <krkabol@gmail.com>

COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp
