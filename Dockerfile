FROM php:8.1.15-cli-alpine

COPY . /srv
WORKDIR /srv

CMD ["/bin/sh", "-c", "php -S 0.0.0.0:8080 -t /srv/public"]
