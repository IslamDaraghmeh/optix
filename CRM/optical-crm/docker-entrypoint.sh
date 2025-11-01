#!/bin/sh
set -e

echo "Waiting for MySQL to be ready..."
until php -r "try { new PDO('mysql:host=mysql_crm;port=3306', 'crm_user', 'crm_password'); echo 'Connected'; exit(0); } catch (PDOException \$e) { exit(1); }" 2>/dev/null; do
  echo "MySQL is unavailable - sleeping"
  sleep 2
done

echo "MySQL is up - executing commands"

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

