#!/bin/sh
set -e

echo "Menunggu database siap..."
tries=0
until php artisan migrate --force; do
  tries=$((tries + 1))
  if [ "$tries" -ge 30 ]; then
    echo "Database tidak dapat dihubungi, berhenti." >&2
    exit 1
  fi
  sleep 2
done

php artisan db:seed --force
php artisan config:cache
php artisan route:cache

exec "$@"
