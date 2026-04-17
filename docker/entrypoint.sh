#!/bin/sh
set -eu

cd /app

# Ensure runtime dirs exist and are writable — volume mounts (pult_storage, pult_db)
# may start empty / with root-owned roots.
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         storage/db \
         bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# First-boot DB: if the volume is empty, create an empty SQLite file. We'll run
# migrations right after; optional one-time seed is handled by DEPLOY.md.
IS_FRESH=0
if [ ! -s storage/db/database.sqlite ]; then
    if [ -f database/database.sqlite ]; then
        echo "entrypoint: seeding storage/db/database.sqlite from committed snapshot"
        cp database/database.sqlite storage/db/database.sqlite
    else
        echo "entrypoint: creating empty storage/db/database.sqlite"
        touch storage/db/database.sqlite
        IS_FRESH=1
    fi
fi
chown www-data:www-data storage/db/database.sqlite
chmod 664 storage/db/database.sqlite

# Laravel warm-up (idempotent; cheap on non-web containers too).
su -s /bin/sh www-data -c 'php artisan config:cache'
su -s /bin/sh www-data -c 'php artisan route:cache'
su -s /bin/sh www-data -c 'php artisan view:cache'
su -s /bin/sh www-data -c 'php artisan migrate --force'
su -s /bin/sh www-data -c 'php artisan storage:link' || true

if [ "$IS_FRESH" = "1" ]; then
    echo "entrypoint: fresh DB — seeding"
    su -s /bin/sh www-data -c 'php artisan db:seed --force' || true
fi

exec "$@"
