#!/bin/sh
set -e

cd /var/www/html

# The project directory is bind-mounted from the host, which hides whatever
# `composer install` produced at image build time. If vendor/ isn't present
# (fresh checkout, or host bind mount), install dependencies now.
if [ ! -f vendor/autoload.php ]; then
    echo "[entrypoint] vendor/ missing - running composer install..."
    composer install --no-interaction --prefer-dist --no-progress
fi

mkdir -p var/cache var/log public/uploads/articles
chown -R www-data:www-data var public/uploads 2>/dev/null || true

exec "$@"
