#!/bin/bash
set -e

# --- Write service-account JSON from env var (Secret Manager) ---
if [ -n "$GOOGLE_CREDENTIALS_JSON" ]; then
    mkdir -p /var/www/html/storage/app/keys
    echo "$GOOGLE_CREDENTIALS_JSON" > /var/www/html/storage/app/keys/firestore-service-account.json
    chown www-data:www-data /var/www/html/storage/app/keys/firestore-service-account.json
    chmod 600 /var/www/html/storage/app/keys/firestore-service-account.json
    export GOOGLE_APPLICATION_CREDENTIALS=/var/www/html/storage/app/keys/firestore-service-account.json
fi

# --- Laravel bootstrap ---
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force 2>/dev/null || true

# --- Start Apache ---
exec apache2-foreground
