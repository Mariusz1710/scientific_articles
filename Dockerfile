FROM php:8.3-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    git \
    unzip \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    $PHPIZE_DEPS

# PHP extensions required by Symfony + Doctrine + PDF/DOCX handling
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        intl \
        opcache \
        pdo \
        pdo_pgsql \
        zip \
        mbstring \
        gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

COPY composer.json composer.lock* ./

# Install dependencies inside the image build (requires network at build time)
RUN if [ -f composer.json ]; then composer install --no-scripts --no-interaction --prefer-dist --no-progress || true; fi

COPY . .

RUN mkdir -p var/cache var/log public/uploads/articles \
    && chown -R www-data:www-data var public/uploads

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
