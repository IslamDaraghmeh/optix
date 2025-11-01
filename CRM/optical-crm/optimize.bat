@echo off
echo ========================================
echo Laravel Performance Optimization Script
echo ========================================
echo.

echo [1/6] Clearing all caches...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo.

echo [2/6] Running composer optimization...
composer install --optimize-autoloader --no-dev
echo.

echo [3/6] Caching configuration...
php artisan config:cache
echo.

echo [4/6] Caching routes...
php artisan route:cache
echo.

echo [5/6] Caching views...
php artisan view:cache
echo.

echo [6/6] Building frontend assets for production...
npm run build
echo.

echo ========================================
echo Optimization complete!
echo ========================================
pause
