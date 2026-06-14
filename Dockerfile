# Interactive REPL image for poking at the SDK with psysh. The SDK requires
# PHP 8.5, so base off the official php:8.5 image and pull composer from the
# official composer image rather than the composer/composer base (which still
# ships PHP 8.4).
FROM php:8.5-cli

WORKDIR /app

# Composer needs git and unzip to fetch and extract dist packages.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set COMPOSER_HOME so global packages are in /root/.composer reliably.
ENV COMPOSER_HOME=/root/.composer
ENV PATH="/root/.composer/vendor/bin:$PATH"

# Remove any previous installation of psy/psysh if present.
RUN composer global remove psy/psysh || true

RUN composer global require psy/psysh

COPY composer.json composer.lock* ./

RUN composer install --no-scripts --no-interaction --no-dev

COPY . .

RUN mkdir -p /root/.config/psysh && \
    echo "<?php require '/app/vendor/autoload.php';" > /root/.config/psysh/config.php

CMD ["composer", "global", "exec", "psysh"]
