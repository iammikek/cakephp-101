FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    git unzip libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts

COPY . .

RUN composer dump-autoload --optimize \
    && cp config/app_local.example.php config/app_local.php \
    && mkdir -p database tmp logs

EXPOSE 8017

CMD ["sh", "-c", "bin/cake migrations migrate && bin/cake server -H 0.0.0.0 -p 8017"]
