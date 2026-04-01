#!/bin/sh
set -e

echo "─── TaskFlow Production Startup ───"

# ─── Wait for database to be ready ───────────────────────────────────────────
echo "Waiting for database connection..."
until php artisan db:monitor 2>/dev/null || php -r "
  \$retries = 30;
  while(\$retries--) {
    try {
      new PDO(
        'pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
      );
      echo 'DB ready' . PHP_EOL;
      exit(0);
    } catch(Exception \$e) {
      echo 'Waiting... ' . \$e->getMessage() . PHP_EOL;
      sleep(2);
    }
  }
  exit(1);
"; do
  sleep 2
done

echo "Database is ready."

# ─── Generate app key if not set ─────────────────────────────────────────────
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
  echo "Generating APP_KEY..."
  php artisan key:generate --force
fi

# ─── Run migrations ───────────────────────────────────────────────────────────
echo "Running migrations..."
php artisan migrate --force --no-interaction

# ─── Seed only if tasks table is empty ───────────────────────────────────────
TASK_COUNT=$(php artisan tinker --execute="echo App\Models\Task::count();" 2>/dev/null | tail -1)
if [ "$TASK_COUNT" = "0" ]; then
  echo "Seeding demo data..."
  php artisan db:seed --force --no-interaction
fi

# ─── Cache config & routes for performance ───────────────────────────────────
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "─── Starting Nginx + PHP-FPM via Supervisor ───"
exec /usr/bin/supervisord -c /etc/supervisord.conf