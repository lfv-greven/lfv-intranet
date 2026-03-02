#!/usr/bin/env sh
set -eu

cd /var/www/html

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
#     php artisan migrate --force
# fi
#
# if [ "${APP_ENV:-production}" = "production" ]; then
#     php artisan config:cache || true
#     php artisan route:cache || true
#     php artisan view:cache || true
# fi

exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
