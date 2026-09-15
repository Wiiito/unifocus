#!/bin/bash
set -e

# Garante que as pastas graváveis do Laravel existam e tenham permissão,
# mesmo quando o bind mount ainda está vazio (primeiro boot).
if [ -f /var/www/html/artisan ]; then
    mkdir -p \
        /var/www/html/storage/framework/{cache/data,sessions,views} \
        /var/www/html/storage/logs \
        /var/www/html/bootstrap/cache
    chmod -R ug+rwX /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
fi

exec "$@"
