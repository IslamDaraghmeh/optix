#!/bin/sh
set -e

# Get database credentials from environment
DB_USER=${DB_USERNAME:-crm_user}
DB_PASS=${DB_PASSWORD:-CHANGE_THIS_PASSWORD}
DB_HOST=${DB_HOST:-mysql_crm}
DB_PORT=${DB_PORT:-3306}

echo "Waiting for MySQL to be ready..."
until php -r "try { new PDO('mysql:host=$DB_HOST;port=$DB_PORT', '$DB_USER', '$DB_PASS'); echo 'Connected'; exit(0); } catch (PDOException \$e) { exit(1); }" 2>/dev/null; do
  echo "MySQL is unavailable - sleeping"
  sleep 2
done

echo "MySQL is up - executing commands"

# Check if vendor directory exists, if not install dependencies
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
  echo "vendor/autoload.php not found. Installing dependencies..."
  cd /var/www/html
  composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
  if [ $? -ne 0 ]; then
    echo "ERROR: composer install failed!"
    exit 1
  fi
fi

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
  echo "Generating Laravel APP_KEY..."
  php artisan key:generate --force
fi

# Clear and cache config
php artisan config:clear
php artisan cache:clear

# Run migrations
php artisan migrate --force

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache

# Optimize
php artisan optimize

echo "Application setup complete!"

exec php-fpm

