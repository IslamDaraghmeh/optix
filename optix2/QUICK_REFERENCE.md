# Quick Reference Guide - Production Operations

Quick commands and procedures for common production tasks.

---

## Table of Contents
1. [Installation Commands](#installation-commands)
2. [Backup & Restore](#backup--restore)
3. [Log Monitoring](#log-monitoring)
4. [Troubleshooting](#troubleshooting)
5. [Maintenance Tasks](#maintenance-tasks)
6. [Security Operations](#security-operations)
7. [Performance Optimization](#performance-optimization)

---

## Installation Commands

### Install Dependencies (Production)
```bash
cd /var/www/optix
composer install --no-dev --optimize-autoloader --classmap-authoritative

# OR use the composer script:
composer production
```

### Set File Permissions
```bash
sudo chown -R www-data:www-data /var/www/optix
sudo find /var/www/optix -type d -exec chmod 755 {} \;
sudo find /var/www/optix -type f -exec chmod 644 {} \;
sudo chmod -R 775 /var/www/optix/storage
sudo chmod 600 /var/www/optix/.env
```

### Create Database
```bash
mysql -u root -p
```
```sql
CREATE DATABASE optix_clinic_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'optix_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT SELECT, INSERT, UPDATE, DELETE ON optix_clinic_prod.* TO 'optix_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Import Database
```bash
mysql -u optix_user -p optix_clinic_prod < database/schema.sql
mysql -u optix_user -p optix_clinic_prod < database/seeds/001_locations.sql
mysql -u optix_user -p optix_clinic_prod < database/seeds/002_users.sql
```

---

## Backup & Restore

### Manual Database Backup
```bash
# Create backup
mysqldump -u optix_user -p optix_clinic_prod | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz

# Verify backup
gunzip -c backup_20251021_120000.sql.gz | head -n 20
```

### Manual File Backup
```bash
# Full backup
tar -czf optix_backup_$(date +%Y%m%d_%H%M%S).tar.gz \
  --exclude='vendor' --exclude='storage/cache' \
  -C /var/www optix

# Backup only uploads
tar -czf uploads_$(date +%Y%m%d_%H%M%S).tar.gz -C /var/www/optix/storage uploads
```

### Restore Database
```bash
# Using the restore script (RECOMMENDED)
sudo /var/www/optix/scripts/restore-database.sh /path/to/backup.sql.gz

# OR manually:
gunzip < backup.sql.gz | mysql -u optix_user -p optix_clinic_prod
```

### Restore Files
```bash
cd /var/www
sudo tar -xzf optix_backup_20251021_120000.tar.gz
sudo chown -R www-data:www-data optix
```

### Automated Backups
```bash
# Setup cron jobs
sudo crontab -e

# Add:
0 2 * * * /var/www/optix/scripts/backup-database.sh
0 3 * * 0 /var/www/optix/scripts/backup-files.sh
0 4 * * 1 /var/www/optix/scripts/maintenance.sh
```

---

## Log Monitoring

### Application Logs
```bash
# View latest logs
tail -f /var/www/optix/storage/logs/app.log

# View last 100 lines
tail -n 100 /var/www/optix/storage/logs/app.log

# Search for errors
grep -i error /var/www/optix/storage/logs/app.log

# Search by date
grep "2025-10-21" /var/www/optix/storage/logs/app.log
```

### Web Server Logs
```bash
# Apache error log
sudo tail -f /var/log/apache2/optix_error.log

# Apache access log
sudo tail -f /var/log/apache2/optix_access.log

# Nginx error log
sudo tail -f /var/log/nginx/optix_error.log

# Nginx access log
sudo tail -f /var/log/nginx/optix_access.log
```

### PHP Logs
```bash
sudo tail -f /var/log/php/error.log

# OR system PHP log
sudo tail -f /var/log/php8.1-fpm.log
```

### MySQL Logs
```bash
sudo tail -f /var/log/mysql/error.log
```

### Clear Old Logs
```bash
# Clear logs older than 30 days
find /var/www/optix/storage/logs -name "*.log" -mtime +30 -delete

# Clear all application logs (careful!)
> /var/www/optix/storage/logs/app.log
```

---

## Troubleshooting

### Check Application Status
```bash
# Check if web server is running
sudo systemctl status apache2    # or nginx
sudo systemctl status mysql

# Check PHP version
php -v

# Check PHP modules
php -m

# Test database connection
mysql -u optix_user -p -e "SELECT 1"
```

### Fix File Permissions Issues
```bash
# Reset all permissions
cd /var/www/optix
sudo chown -R www-data:www-data .
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;
sudo chmod -R 775 storage
sudo chmod 600 .env
```

### Fix "500 Internal Server Error"
```bash
# 1. Check Apache error log
sudo tail -n 50 /var/log/apache2/optix_error.log

# 2. Verify .htaccess exists
ls -la /var/www/optix/.htaccess
ls -la /var/www/optix/public/.htaccess

# 3. Check mod_rewrite is enabled
sudo a2enmod rewrite
sudo systemctl restart apache2

# 4. Verify .env file exists
ls -la /var/www/optix/.env

# 5. Check storage permissions
ls -ld /var/www/optix/storage
```

### Fix Database Connection Issues
```bash
# Test database connection
mysql -u optix_user -p optix_clinic_prod -e "SELECT COUNT(*) FROM users;"

# Check database credentials in .env
cat /var/www/optix/.env | grep DB_

# Verify MySQL is running
sudo systemctl status mysql
```

### Clear Cache
```bash
# Using composer script
cd /var/www/optix
composer clear-cache

# OR manually
rm -rf /var/www/optix/storage/cache/*

# Clear OPcache (restart web server)
sudo systemctl restart apache2
```

### Fix Session Issues
```bash
# Check session directory
ls -ld /var/lib/php/sessions

# Fix permissions
sudo chmod 1733 /var/lib/php/sessions

# Clear sessions
sudo rm -rf /var/lib/php/sessions/*
```

---

## Maintenance Tasks

### Update Composer Dependencies
```bash
cd /var/www/optix

# Update all dependencies
composer update --no-dev

# Update specific package
composer update phpmailer/phpmailer --no-dev

# After update, optimize autoloader
composer dump-autoload --optimize --classmap-authoritative
```

### Optimize Database
```bash
# Using maintenance script
sudo /var/www/optix/scripts/maintenance.sh

# OR manually optimize all tables
mysql -u optix_user -p optix_clinic_prod -e "
  SELECT CONCAT('OPTIMIZE TABLE \`', table_name, '\`;')
  FROM information_schema.tables
  WHERE table_schema='optix_clinic_prod'
" | tail -n +2 | mysql -u optix_user -p optix_clinic_prod
```

### Check Disk Space
```bash
# Overall disk usage
df -h

# Application directory size
du -sh /var/www/optix

# Storage directory breakdown
du -h /var/www/optix/storage/* | sort -h

# Database size
mysql -u optix_user -p -e "
  SELECT table_schema AS 'Database',
         ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
  FROM information_schema.TABLES
  WHERE table_schema = 'optix_clinic_prod'
  GROUP BY table_schema;
"
```

### Restart Services
```bash
# Restart Apache
sudo systemctl restart apache2

# Restart Nginx
sudo systemctl restart nginx

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm

# Restart MySQL
sudo systemctl restart mysql

# Restart all web services
sudo systemctl restart apache2 mysql
```

---

## Security Operations

### Change Database Password
```bash
mysql -u root -p
```
```sql
ALTER USER 'optix_user'@'localhost' IDENTIFIED BY 'NEW_STRONG_PASSWORD';
FLUSH PRIVILEGES;
EXIT;
```

Then update `/var/www/optix/.env`:
```bash
nano /var/www/optix/.env
# Update DB_PASSWORD=NEW_STRONG_PASSWORD
```

### Check Failed Login Attempts
```sql
mysql -u optix_user -p optix_clinic_prod

SELECT * FROM failed_login_attempts
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY created_at DESC;
```

### Review Audit Logs
```sql
mysql -u optix_user -p optix_clinic_prod

-- Recent activity
SELECT * FROM audit_logs
ORDER BY created_at DESC
LIMIT 100;

-- Suspicious activity
SELECT * FROM audit_logs
WHERE action IN ('delete', 'update')
AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY created_at DESC;
```

### Check Security Headers
```bash
curl -I https://your-domain.com

# OR use online tool
# https://securityheaders.com
```

### Update SSL Certificate
```bash
# Renew Let's Encrypt certificate
sudo certbot renew

# Test renewal
sudo certbot renew --dry-run

# Check certificate expiry
openssl s_client -connect your-domain.com:443 -servername your-domain.com 2>/dev/null | \
  openssl x509 -noout -dates
```

### Scan for Malware (Optional)
```bash
# Install ClamAV
sudo apt install clamav clamav-daemon

# Update virus definitions
sudo freshclam

# Scan application
sudo clamscan -r /var/www/optix
```

---

## Performance Optimization

### Enable OPcache
Edit `/etc/php/8.1/apache2/php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

### Check OPcache Status
Create a PHP file temporarily:
```bash
echo "<?php phpinfo(); ?>" > /var/www/optix/public/info.php
```
Visit: `https://your-domain.com/info.php` (search for "OPcache")
**DELETE THIS FILE IMMEDIATELY AFTER CHECKING**
```bash
rm /var/www/optix/public/info.php
```

### Optimize MySQL
Edit `/etc/mysql/mysql.conf.d/mysqld.cnf`:
```ini
[mysqld]
innodb_buffer_pool_size=1G
innodb_log_file_size=256M
innodb_flush_log_at_trx_commit=2
innodb_flush_method=O_DIRECT
max_connections=200
query_cache_size=0
query_cache_type=0
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

### Analyze Slow Queries
```bash
# Enable slow query log in MySQL config
# log_slow_queries = /var/log/mysql/slow-queries.log
# long_query_time = 2

# View slow queries
sudo tail -f /var/log/mysql/slow-queries.log
```

### Monitor Server Resources
```bash
# CPU and Memory
top

# OR use htop (install: sudo apt install htop)
htop

# Disk I/O
iostat -x 1

# Network
iftop
```

---

## Common One-Liners

```bash
# Count application errors in last hour
grep -c "ERROR" /var/www/optix/storage/logs/app.log

# Find largest files in uploads
find /var/www/optix/storage/uploads -type f -exec du -h {} + | sort -rh | head -20

# Count database records
mysql -u optix_user -p optix_clinic_prod -e "
  SELECT table_name, table_rows
  FROM information_schema.tables
  WHERE table_schema = 'optix_clinic_prod'
  ORDER BY table_rows DESC;
"

# Check if backups ran today
ls -lh /var/backups/optix/database/ | grep $(date +%Y%m%d)

# Find files modified in last 24 hours
find /var/www/optix -type f -mtime -1 -ls

# Test email configuration (requires mail command)
echo "Test email from Optix" | mail -s "Test" your-email@domain.com

# Generate strong password
openssl rand -base64 32

# Check web server connections
netstat -an | grep :80 | wc -l
netstat -an | grep :443 | wc -l
```

---

## Emergency Procedures

### Put Site in Maintenance Mode
```bash
# Create maintenance flag
touch /var/www/optix/.maintenance

# Create maintenance page in public/index.php (backup first)
cp /var/www/optix/public/index.php /var/www/optix/public/index.php.backup
```

Add at the top of index.php:
```php
if (file_exists(__DIR__ . '/../.maintenance')) {
    http_response_code(503);
    die('Site is under maintenance. Please check back soon.');
}
```

### Take Site Offline (Block Traffic)
```bash
# Stop web server
sudo systemctl stop apache2

# OR use firewall
sudo ufw deny 80/tcp
sudo ufw deny 443/tcp
```

### Emergency Rollback
```bash
# 1. Restore database
gunzip < /var/backups/optix/database/latest_backup.sql.gz | \
  mysql -u optix_user -p optix_clinic_prod

# 2. Restore files
cd /var/www
sudo tar -xzf /var/backups/optix/files/latest_backup.tar.gz
sudo chown -R www-data:www-data optix

# 3. Restart services
sudo systemctl restart apache2 mysql
```

---

## Useful Paths

| Item | Path |
|------|------|
| Application Root | `/var/www/optix` |
| Public Directory | `/var/www/optix/public` |
| Environment File | `/var/www/optix/.env` |
| Logs | `/var/www/optix/storage/logs` |
| Uploads | `/var/www/optix/storage/uploads` |
| Cache | `/var/www/optix/storage/cache` |
| Scripts | `/var/www/optix/scripts` |
| Backups | `/var/backups/optix` |
| Apache Config | `/etc/apache2/sites-available/optix.conf` |
| Nginx Config | `/etc/nginx/sites-available/optix` |
| PHP Config | `/etc/php/8.1/apache2/php.ini` |
| MySQL Config | `/etc/mysql/mysql.conf.d/mysqld.cnf` |

---

## Support Commands

```bash
# Generate system info report
cat << EOF
System Information
==================
Hostname: $(hostname)
OS: $(lsb_release -d | cut -f2)
Kernel: $(uname -r)
PHP: $(php -v | head -n1)
MySQL: $(mysql -V)
Web Server: $(apache2 -v 2>/dev/null | head -n1 || nginx -v 2>&1)
Disk Space: $(df -h / | tail -n1 | awk '{print $4 " free of " $2}')
Memory: $(free -h | grep Mem: | awk '{print $3 " used of " $2}')
Uptime: $(uptime -p)
EOF
```

---

**Last Updated**: 2025-10-21
**Version**: 1.0
