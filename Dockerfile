FROM node:24-alpine AS assets
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM php:8.5-fpm-alpine AS runtime
RUN apk add --no-cache bash icu-libs libzip libpng libjpeg-turbo freetype libwebp mariadb-client \
    && apk add --no-cache --virtual .build-deps icu-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j1 bcmath exif gd intl pcntl pdo_mysql zip \
    && apk del .build-deps
COPY deploy/php.ini /usr/local/etc/php/conf.d/99-bk.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-progress
COPY . .
COPY --from=assets /build/public/build ./public/build
RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && chmod +x deploy/entrypoint.sh \
    && mkdir -p storage/app/private storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && ln -sfn /var/www/html/storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache
ENTRYPOINT ["deploy/entrypoint.sh"]
CMD ["php-fpm", "-F"]
