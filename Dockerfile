FROM ghcr.io/biodiversity-cz/php-fpm-noroot-socket:main@sha256:266779d21db3607a2e1df7fc9392debbaee09a32fef9edcb0792e0728c652d1a

MAINTAINER Petr Novotný <krkabol@gmail.com>

COPY  --chown=www:www htdocs /app
RUN chmod -R 777 /app/temp
