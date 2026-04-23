FROM docker.cnb.cool/larva-cool/php:8.4-alpine

ARG ENV=prod

WORKDIR /app

COPY . /app

RUN mv .env.${ENV} /app/.env \
    && composer install --prefer-dist --no-progress --optimize-autoloader

RUN php artisan octane:install --server=frankenphp --force --silent

VOLUME [ "/app/runtime/logs" ]

EXPOSE 8787/tcp

CMD ["php", "artisan", "octane:start", "--host=0.0.0.0", "--port=8787"]
