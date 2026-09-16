#!/bin/sh
set -eu
cd /var/www/html
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/promethee-distinctions bootstrap/cache
# Image uploads must survive Docker image rebuilds. The public directory is
# rebuilt, whereas storage is a named volume, so expose the persistent folder.
mkdir -p public/promethee-assets
ln -sfn ../../storage/app/promethee-distinctions public/promethee-assets/distinctions
# This container is an isolated local copy. Never load the hosting backup's configuration.
if [ ! -f .env ]; then
  printf 'APP_NAME="Air Inter — Prométhée"\nAPP_KEY=base64:%s\nAPP_ENV=local\nAPP_DEBUG=false\nAPP_URL=http://localhost:8088\nDB_CONNECTION=mysql\nDB_HOST=db\nDB_DATABASE=promethee\nDB_USERNAME=promethee\nDB_PASSWORD=local-promethee-only\nDB_PREFIX=phpvms7_\nCACHE_DRIVER=file\nSESSION_DRIVER=file\nQUEUE_CONNECTION=sync\nMAIL_MAILER=log\nPROMETHEE_LOCAL=true\nPROMETHEE_LOCAL_PASSWORD=promethee-local\nDEFAULT_THEME=seven\n' "$(php -r 'echo base64_encode(random_bytes(32));')" > .env
fi
# Remove only generated caches in this container volume.
find bootstrap/cache -maxdepth 1 -name '*.php' -type f -delete
# The storage volume survives image rebuilds. Clear compiled Blade templates
# too, otherwise a template from an older image can render an empty response.
php artisan view:clear
if ! MYSQL_PWD="$DB_PASSWORD" mariadb -h db -u "$DB_USERNAME" "$DB_DATABASE" -N -e "SHOW TABLES LIKE 'phpvms7_users'" | grep -q phpvms7_users; then
  MYSQL_PWD="$DB_PASSWORD" mariadb -h db -u "$DB_USERNAME" "$DB_DATABASE" < db_backup.sql
  php /opt/promethee/localize.php
fi
php artisan migrate --force
php artisan promethee:local-user
exec php -S 0.0.0.0:8000 -t public server.php
