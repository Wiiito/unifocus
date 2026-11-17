#!/bin/bash
set -e
cd /var/www/html

# O Render gera APP_KEY como base64 puro (256 bits); o Laravel espera o prefixo.
if [[ -n "$APP_KEY" && "$APP_KEY" != base64:* ]]; then
    export APP_KEY="base64:${APP_KEY}"
fi

# URL pública: usa a do Render quando APP_URL não foi definida.
export APP_URL="${APP_URL:-$RENDER_EXTERNAL_URL}"
export GOOGLE_REDIRECT_URI="${GOOGLE_REDIRECT_URI:-${APP_URL}/auth/google/callback}"

# Porta exigida pelo Render.
sed -i "s/listen [0-9]*;/listen ${PORT:-10000};/" /etc/nginx/http.d/default.conf

php artisan migrate --force
php artisan optimize

chown -R www-data:www-data storage bootstrap/cache

exec /usr/bin/supervisord -c /etc/supervisord.conf
