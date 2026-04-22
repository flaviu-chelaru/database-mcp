FROM php:8.4-cli-alpine AS builder

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache jq

WORKDIR /app

COPY composer.json composer.lock ./

RUN install-php-extensions $(jq -r \
    '(.require // {}) + (."require-dev" // {}) | keys[] | select(startswith("ext-")) | sub("^ext-";"")' \
    composer.json)

RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader \
    && rm -rf /root/.composer/cache

COPY . .

RUN if [ ! -f .env ]; then cp .env.example .env; fi \
    && rm -f bootstrap/cache/packages.php bootstrap/cache/services.php \
    && php artisan package:discover --ansi \
    && php artisan key:generate --ansi

# Runtime stage -- no jq, composer, or install-php-extensions
FROM php:8.4-cli-alpine AS base

# Copy extension shared libraries and their runtime dependencies
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=builder /usr/lib/ /usr/lib/

WORKDIR /app
COPY --from=builder /app /app

ENV APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=sqlite \
    DB_CONNECTIONS='{}'

ENTRYPOINT ["php", "artisan"]
CMD ["mcp:start", "database"]

# Development target with Node.js for mcp:inspector
FROM base AS dev

COPY --from=node:22-alpine /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-alpine /usr/local/lib/node_modules /usr/local/lib/node_modules

RUN composer install --no-scripts --no-interaction --prefer-dist --optimize-autoloader \
    && rm -rf /root/.composer/cache

RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

ENV APP_ENV=local \
    APP_DEBUG=true
