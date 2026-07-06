#!/usr/bin/env bash
set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Wait for Postgres to accept connections before running migrations.
echo "Waiting for database at ${DB_HOST:-postgres}:${DB_PORT:-5432}..."
until php -r "new PDO('pgsql:host=${DB_HOST:-postgres};port=${DB_PORT:-5432}', '${DB_USERNAME:-metrics_user}', '${DB_PASSWORD:-metrics_password}');" 2>/dev/null; do
  sleep 2
done
echo "Database is up."

if ! grep -q "^APP_KEY=base64" .env; then
    php artisan key:generate --force
fi

if [ -z "${JWT_SECRET}" ] && ! grep -q "^JWT_SECRET=." .env; then
    php artisan jwt:secret --force
fi

php artisan migrate --force

if [ "${L5_SWAGGER_GENERATE_ALWAYS:-true}" = "true" ]; then
    php artisan l5-swagger:generate || true
fi

exec "$@"
