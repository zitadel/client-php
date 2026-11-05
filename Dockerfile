FROM composer/composer:2@sha256:1b9f7b574c833b0f426129f994ef757b7c7533000b6c93fb60c8c82dd06f16fc

WORKDIR /app

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
