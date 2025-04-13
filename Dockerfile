  FROM composer/composer:2

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
