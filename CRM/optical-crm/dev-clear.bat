@echo off
echo ========================================
echo Laravel Development Cache Clear Script
echo ========================================
echo.

echo Clearing all caches for development...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo.

echo ========================================
echo All caches cleared! Ready for development.
echo ========================================
pause
