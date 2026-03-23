#!/usr/bin/env sh
set -eu

cd /var/www/html

ensure_runtime_permissions() {
    mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R ug+rwX storage bootstrap/cache
}

ensure_runtime_permissions

envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

if [ "${ENABLE_QUEUE_WORKER:-true}" != "true" ]; then
    rm -f /etc/supervisor/conf.d/queue.conf
fi

# if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
#     php artisan migrate --force
# fi

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

ensure_runtime_permissions

exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
