# ============================================================
# Laravel on Cloud Run — PHP 8.2 + Apache
# ============================================================
FROM php:8.2-apache

# ------ System deps + gRPC extension ------
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions grpc zip opcache \
    && apt-get update && apt-get install -y --no-install-recommends unzip curl git \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# ------ Apache config ------
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite headers

# ------ PHP production config ------
COPY docker/php.ini /usr/local/etc/php/conf.d/99-production.ini

# ------ Composer ------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ------ Application code ------
WORKDIR /var/www/html
COPY . .

# ------ Install dependencies (no dev) ------
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-req=ext-grpc

# ------ File permissions ------
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# ------ Create SQLite database (for sessions/cache) ------
RUN mkdir -p database && touch database/database.sqlite \
    && chown www-data:www-data database/database.sqlite

# ------ Cloud Run uses PORT env var ------
ENV PORT=8080
RUN sed -i "s/80/${PORT}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 8080

# ------ Startup script ------
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
