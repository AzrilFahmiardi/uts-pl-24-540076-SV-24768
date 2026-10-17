FROM dunglas/frankenphp:1.10-php8.3-alpine

RUN install-php-extensions pdo_mysql pcntl opcache zip

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Dependensi disalin dan dipasang lebih dulu supaya layer ini ter-cache
# selama composer.json / composer.lock tidak berubah.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY Caddyfile /etc/caddy/Caddyfile
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
