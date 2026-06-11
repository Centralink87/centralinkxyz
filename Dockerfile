FROM dunglas/frankenphp:1-php8.4-alpine

# Extensions PHP (install-php-extensions gère les dépendances système automatiquement)
RUN install-php-extensions intl pdo_pgsql pgsql zip opcache

# Node.js + outils système
RUN apk add --no-cache git unzip nodejs npm bash

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# start.sh cherche "caddy" en premier — on crée un alias vers frankenphp
RUN ln -sf "$(which frankenphp)" /usr/local/bin/caddy

WORKDIR /app

# Dépendances PHP (en deux étapes pour profiter du cache Docker)
COPY composer.json composer.lock symfony.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-scripts --optimize-autoloader

# Code source
COPY . .

# Compilation des assets en prod
ENV APP_ENV=prod
ENV APP_SECRET=docker-build-placeholder
RUN php bin/console importmap:install --no-debug \
    && php bin/console assets:install public --env=prod --no-debug \
    && php bin/console asset-map:compile --env=prod --no-debug \
    && php bin/console cache:clear --env=prod --no-debug

RUN chmod +x start.sh

EXPOSE 8000

ENTRYPOINT ["./start.sh"]
