# Production Deployment Guide

## Overview
This guide provides comprehensive instructions for deploying the Optix Clinic Management System to a production environment.

---

## Pre-Deployment Checklist

### 1. Server Requirements Verification

- [ ] PHP 8.1 or higher installed
- [ ] MySQL 8.0 or higher installed
- [ ] Apache with mod_rewrite enabled OR Nginx
- [ ] Composer installed
- [ ] SSL certificate configured (HTTPS)
- [ ] PHP extensions installed:
  - [ ] PDO
  - [ ] pdo_mysql
  - [ ] mbstring
  - [ ] gd
  - [ ] curl
  - [ ] zip
  - [ ] openssl

### 2. Security Hardening

#### Server Configuration
- [ ] Disable root SSH login
- [ ] Configure firewall (UFW/iptables)
  - [ ] Allow HTTP (80)
  - [ ] Allow HTTPS (443)
  - [ ] Allow SSH (22) - restrict to specific IPs if possible
  - [ ] Block all other incoming ports
- [ ] Configure fail2ban for brute force protection
- [ ] Set up automatic security updates
- [ ] Disable unnecessary services
- [ ] Configure SELinux/AppArmor if available

#### PHP Configuration
- [ ] Edit `/etc/php/8.1/apache2/php.ini` (or appropriate path):
  ```ini
  display_errors = Off
  log_errors = On
  error_log = /var/log/php/error.log
  expose_php = Off
  max_execution_time = 300
  max_input_time = 300
  memory_limit = 256M
  upload_max_filesize = 10M
  post_max_size = 10M
  session.cookie_httponly = 1
  session.cookie_secure = 1
  session.cookie_samesite = Strict
  session.use_strict_mode = 1
  allow_url_fopen = Off
  allow_url_include = Off
  disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
  ```

#### Database Security
- [ ] Create dedicated MySQL user (not root):
  ```sql
  CREATE USER 'optix_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
  GRANT SELECT, INSERT, UPDATE, DELETE ON optix_clinic_prod.* TO 'optix_user'@'localhost';
  FLUSH PRIVILEGES;
  ```
- [ ] Disable remote MySQL access if not needed
- [ ] Change MySQL root password
- [ ] Remove test databases
- [ ] Configure MySQL bind address to localhost only

### 3. File System Preparation

#### Directory Setup
```bash
# Navigate to web root
cd /var/www

# Clone or upload the project
# git clone <repository> optix
# OR upload files via SFTP/SCP

# Set correct ownership
sudo chown -R www-data:www-data /var/www/optix

# Set directory permissions
sudo find /var/www/optix -type d -exec chmod 755 {} \;
sudo find /var/www/optix -type f -exec chmod 644 {} \;

# Set writable directories
sudo chmod -R 775 /var/www/optix/storage
sudo chmod -R 775 /var/www/optix/storage/logs
sudo chmod -R 775 /var/www/optix/storage/uploads
sudo chmod -R 775 /var/www/optix/storage/cache
```

#### Create Required Directories
- [ ] Verify storage directories exist:
  ```bash
  mkdir -p /var/www/optix/storage/logs
  mkdir -p /var/www/optix/storage/uploads
  mkdir -p /var/www/optix/storage/cache
  ```

### 4. Environment Configuration

- [ ] Copy production environment file:
  ```bash
  cp .env.production .env
  ```
- [ ] Edit `.env` with production values:
  - [ ] `APP_ENV=production`
  - [ ] `APP_DEBUG=false`
  - [ ] `APP_URL=https://your-domain.com`
  - [ ] Update database credentials
  - [ ] Update email credentials
  - [ ] `SESSION_SECURE=true`
  - [ ] Set strong, unique passwords (minimum 16 characters)
  - [ ] Update timezone to match your location

- [ ] Secure the .env file:
  ```bash
  chmod 600 /var/www/optix/.env
  ```

### 5. Dependency Installation

- [ ] Install Composer dependencies (production mode):
  ```bash
  cd /var/www/optix
  composer install --no-dev --optimize-autoloader --classmap-authoritative
  ```

### 6. Database Setup

- [ ] Create production database:
  ```sql
  CREATE DATABASE optix_clinic_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
  ```

- [ ] Import schema:
  ```bash
  mysql -u optix_user -p optix_clinic_prod < database/schema.sql
  ```

- [ ] Import seed data (if needed):
  ```bash
  mysql -u optix_user -p optix_clinic_prod < database/seeds/001_locations.sql
  mysql -u optix_user -p optix_clinic_prod < database/seeds/002_users.sql
  mysql -u optix_user -p optix_clinic_prod < database/seeds/003_sample_data.sql
  ```

- [ ] **IMPORTANT**: Change default admin password immediately:
  ```sql
  -- Login to the application and change password, or run:
  -- (Generate new hash at: https://bcrypt-generator.com/ with cost 12)
  UPDATE users SET password = '$2y$12$NEW_HASH_HERE' WHERE email = 'admin@optixclinic.com';
  ```

### 7. Web Server Configuration

#### Apache Configuration

- [ ] Create virtual host file: `/etc/apache2/sites-available/optix.conf`
  ```apache
  <VirtualHost *:80>
      ServerName your-domain.com
      ServerAlias www.your-domain.com
      Redirect permanent / https://your-domain.com/
  </VirtualHost>

  <VirtualHost *:443>
      ServerName your-domain.com
      ServerAlias www.your-domain.com
      DocumentRoot /var/www/optix/public

      <Directory /var/www/optix/public>
          Options -Indexes +FollowSymLinks
          AllowOverride All
          Require all granted
      </Directory>

      # SSL Configuration
      SSLEngine on
      SSLCertificateFile /etc/ssl/certs/your-domain.crt
      SSLCertificateKeyFile /etc/ssl/private/your-domain.key
      SSLCertificateChainFile /etc/ssl/certs/ca-bundle.crt

      # Security Headers
      Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
      Header always set X-Frame-Options "SAMEORIGIN"
      Header always set X-Content-Type-Options "nosniff"
      Header always set X-XSS-Protection "1; mode=block"

      # Logging
      ErrorLog ${APACHE_LOG_DIR}/optix_error.log
      CustomLog ${APACHE_LOG_DIR}/optix_access.log combined
  </VirtualHost>
  ```

- [ ] Enable required Apache modules:
  ```bash
  sudo a2enmod rewrite
  sudo a2enmod ssl
  sudo a2enmod headers
  sudo a2enmod expires
  sudo a2enmod deflate
  ```

- [ ] Enable the site:
  ```bash
  sudo a2ensite optix
  sudo a2dissite 000-default
  ```

- [ ] Test configuration:
  ```bash
  sudo apache2ctl configtest
  ```

- [ ] Restart Apache:
  ```bash
  sudo systemctl restart apache2
  ```

#### Nginx Configuration (Alternative)

- [ ] Create server block: `/etc/nginx/sites-available/optix`
  ```nginx
  # Redirect HTTP to HTTPS
  server {
      listen 80;
      listen [::]:80;
      server_name your-domain.com www.your-domain.com;
      return 301 https://$server_name$request_uri;
  }

  # HTTPS Server
  server {
      listen 443 ssl http2;
      listen [::]:443 ssl http2;
      server_name your-domain.com www.your-domain.com;
      root /var/www/optix/public;
      index index.php;

      # SSL Configuration
      ssl_certificate /etc/ssl/certs/your-domain.crt;
      ssl_certificate_key /etc/ssl/private/your-domain.key;
      ssl_protocols TLSv1.2 TLSv1.3;
      ssl_ciphers HIGH:!aNULL:!MD5;
      ssl_prefer_server_ciphers on;

      # Security Headers
      add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;
      add_header X-Frame-Options "SAMEORIGIN" always;
      add_header X-Content-Type-Options "nosniff" always;
      add_header X-XSS-Protection "1; mode=block" always;

      # Logging
      access_log /var/log/nginx/optix_access.log;
      error_log /var/log/nginx/optix_error.log;

      # Root location
      location / {
          try_files $uri $uri/ /index.php?$query_string;
      }

      # PHP handling
      location ~ \.php$ {
          include snippets/fastcgi-php.conf;
          fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
          fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
          include fastcgi_params;
      }

      # Deny access to hidden files
      location ~ /\. {
          deny all;
      }

      # Deny access to sensitive files
      location ~* \.(env|sql|log|ini|sh|bak|old)$ {
          deny all;
      }

      # Static file caching
      location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
          expires 1y;
          add_header Cache-Control "public, immutable";
      }
  }
  ```

- [ ] Enable the site:
  ```bash
  sudo ln -s /etc/nginx/sites-available/optix /etc/nginx/sites-enabled/
  sudo rm /etc/nginx/sites-enabled/default
  ```

- [ ] Test configuration:
  ```bash
  sudo nginx -t
  ```

- [ ] Restart Nginx:
  ```bash
  sudo systemctl restart nginx
  ```

### 8. SSL/HTTPS Setup

#### Using Let's Encrypt (Recommended - Free)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache  # For Apache
# OR
sudo apt install certbot python3-certbot-nginx   # For Nginx

# Obtain certificate
sudo certbot --apache -d your-domain.com -d www.your-domain.com  # Apache
# OR
sudo certbot --nginx -d your-domain.com -d www.your-domain.com   # Nginx

# Test auto-renewal
sudo certbot renew --dry-run
```

- [ ] SSL certificate installed and working
- [ ] Auto-renewal configured
- [ ] HSTS header enabled in .htaccess (after HTTPS is confirmed working)

### 9. Monitoring and Logging

#### Log Files Setup
- [ ] Create log rotation configuration: `/etc/logrotate.d/optix`
  ```
  /var/www/optix/storage/logs/*.log {
      daily
      missingok
      rotate 14
      compress
      delaycompress
      notifempty
      create 0640 www-data www-data
      sharedscripts
  }
  ```

- [ ] Set up monitoring for:
  - [ ] Disk space
  - [ ] CPU usage
  - [ ] Memory usage
  - [ ] Database connections
  - [ ] PHP errors
  - [ ] Application errors

#### Error Monitoring
- [ ] Configure error log monitoring:
  ```bash
  tail -f /var/www/optix/storage/logs/app.log
  tail -f /var/log/apache2/optix_error.log  # Or nginx
  tail -f /var/log/php/error.log
  ```

### 10. Backup Configuration

#### Database Backup Script
- [ ] Create backup script: `/usr/local/bin/backup-optix-db.sh`
  ```bash
  #!/bin/bash

  # Configuration
  DB_NAME="optix_clinic_prod"
  DB_USER="optix_user"
  DB_PASS="YOUR_PASSWORD"
  BACKUP_DIR="/var/backups/optix"
  DATE=$(date +%Y%m%d_%H%M%S)

  # Create backup directory if not exists
  mkdir -p $BACKUP_DIR

  # Create backup
  mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/optix_db_$DATE.sql.gz

  # Keep only last 30 days of backups
  find $BACKUP_DIR -name "optix_db_*.sql.gz" -mtime +30 -delete

  # Log backup
  echo "$(date): Database backup completed - optix_db_$DATE.sql.gz" >> /var/log/optix_backup.log
  ```

- [ ] Make script executable:
  ```bash
  sudo chmod +x /usr/local/bin/backup-optix-db.sh
  ```

#### File Backup Script
- [ ] Create file backup script: `/usr/local/bin/backup-optix-files.sh`
  ```bash
  #!/bin/bash

  # Configuration
  SOURCE_DIR="/var/www/optix"
  BACKUP_DIR="/var/backups/optix"
  DATE=$(date +%Y%m%d_%H%M%S)

  # Create backup directory if not exists
  mkdir -p $BACKUP_DIR

  # Create backup (exclude vendor and cache)
  tar -czf $BACKUP_DIR/optix_files_$DATE.tar.gz \
      --exclude='vendor' \
      --exclude='storage/cache' \
      --exclude='storage/logs/*.log' \
      -C /var/www optix

  # Keep only last 30 days of backups
  find $BACKUP_DIR -name "optix_files_*.tar.gz" -mtime +30 -delete

  # Log backup
  echo "$(date): File backup completed - optix_files_$DATE.tar.gz" >> /var/log/optix_backup.log
  ```

- [ ] Make script executable:
  ```bash
  sudo chmod +x /usr/local/bin/backup-optix-files.sh
  ```

#### Schedule Automated Backups
- [ ] Add to crontab (`sudo crontab -e`):
  ```cron
  # Backup database daily at 2 AM
  0 2 * * * /usr/local/bin/backup-optix-db.sh

  # Backup files weekly on Sunday at 3 AM
  0 3 * * 0 /usr/local/bin/backup-optix-files.sh
  ```

### 11. Performance Optimization

#### PHP OPcache
- [ ] Enable OPcache in `php.ini`:
  ```ini
  opcache.enable=1
  opcache.memory_consumption=256
  opcache.interned_strings_buffer=16
  opcache.max_accelerated_files=10000
  opcache.revalidate_freq=60
  opcache.fast_shutdown=1
  ```

#### Database Optimization
- [ ] Configure MySQL for production in `/etc/mysql/mysql.conf.d/mysqld.cnf`:
  ```ini
  [mysqld]
  innodb_buffer_pool_size=1G
  innodb_log_file_size=256M
  innodb_flush_log_at_trx_commit=2
  innodb_flush_method=O_DIRECT
  max_connections=200
  ```

- [ ] Restart MySQL:
  ```bash
  sudo systemctl restart mysql
  ```

### 12. Testing

#### Functionality Testing
- [ ] Test homepage loads correctly
- [ ] Test login functionality
- [ ] Test all major features:
  - [ ] Patient management
  - [ ] Appointments
  - [ ] Inventory
  - [ ] Point of Sale
  - [ ] Reports
- [ ] Test file uploads
- [ ] Test email sending
- [ ] Test PDF generation

#### Security Testing
- [ ] Verify HTTPS is working
- [ ] Test that .env file is not accessible via browser
- [ ] Test that vendor directory is not accessible
- [ ] Test that log files are not accessible
- [ ] Verify security headers are present (use https://securityheaders.com)
- [ ] Test SQL injection protection
- [ ] Test XSS protection
- [ ] Test CSRF protection

#### Performance Testing
- [ ] Run load tests
- [ ] Check page load times
- [ ] Monitor database query performance
- [ ] Check server resource usage

---

## Post-Deployment Tasks

### 1. Initial Configuration
- [ ] Login with default admin account
- [ ] Change admin password immediately
- [ ] Update admin email address
- [ ] Configure email settings and test
- [ ] Set up clinic locations
- [ ] Create additional user accounts
- [ ] Delete or disable default test accounts

### 2. Application Configuration
- [ ] Configure tax rates
- [ ] Set up insurance providers
- [ ] Add product categories
- [ ] Configure appointment slots
- [ ] Set up email templates
- [ ] Configure low stock thresholds

### 3. Documentation
- [ ] Document server credentials (store securely)
- [ ] Document database credentials
- [ ] Document backup locations
- [ ] Create disaster recovery plan
- [ ] Document maintenance procedures

### 4. Monitoring Setup
- [ ] Set up uptime monitoring (e.g., UptimeRobot)
- [ ] Configure email alerts for errors
- [ ] Set up performance monitoring
- [ ] Configure backup success notifications

---

## Maintenance Schedule

### Daily
- [ ] Check error logs
- [ ] Monitor disk space
- [ ] Verify backups completed successfully

### Weekly
- [ ] Review security logs
- [ ] Check for failed login attempts
- [ ] Monitor database size
- [ ] Review performance metrics

### Monthly
- [ ] Update Composer dependencies:
  ```bash
  composer update --no-dev
  ```
- [ ] Review and optimize slow database queries
- [ ] Check SSL certificate expiry
- [ ] Review user access and permissions

### Quarterly
- [ ] Security audit
- [ ] Performance review
- [ ] Backup restoration test
- [ ] Update PHP and MySQL if needed

---

## Disaster Recovery

### Database Restoration
```bash
# Restore from backup
gunzip < /var/backups/optix/optix_db_YYYYMMDD_HHMMSS.sql.gz | mysql -u optix_user -p optix_clinic_prod
```

### File Restoration
```bash
# Restore files from backup
cd /var/www
sudo tar -xzf /var/backups/optix/optix_files_YYYYMMDD_HHMMSS.tar.gz
sudo chown -R www-data:www-data optix
```

---

## Troubleshooting

### Common Issues

**Issue**: 500 Internal Server Error
- Check Apache/Nginx error logs
- Verify .htaccess is properly configured
- Check file permissions
- Verify .env file exists and is readable

**Issue**: Database connection failed
- Verify database credentials in .env
- Check if MySQL is running: `sudo systemctl status mysql`
- Verify database user permissions
- Check MySQL error log: `/var/log/mysql/error.log`

**Issue**: Session issues
- Verify session directory is writable
- Check session configuration in php.ini
- Clear session files if needed

**Issue**: Email not sending
- Verify SMTP credentials in .env
- Check firewall allows outbound SMTP connections
- Review mail logs
- Test with a simple mail test script

---

## Security Incident Response

If you suspect a security breach:

1. **Immediate Actions**:
   - Take the application offline if necessary
   - Change all passwords (database, admin, email)
   - Review access logs
   - Check for unauthorized database changes

2. **Investigation**:
   - Review audit logs in database
   - Check file modification times
   - Review error logs
   - Analyze web server access logs

3. **Recovery**:
   - Restore from clean backup if needed
   - Update all dependencies
   - Patch vulnerabilities
   - Monitor closely for 48 hours

4. **Prevention**:
   - Document what happened
   - Implement additional security measures
   - Update security procedures

---

## Support and Resources

- **Documentation**: `/var/www/optix/README.md`
- **Error Logs**: `/var/www/optix/storage/logs/`
- **Server Logs**: `/var/log/apache2/` or `/var/log/nginx/`
- **PHP Logs**: `/var/log/php/error.log`

---

## Final Checklist

Before going live, verify ALL items below:

- [ ] SSL certificate installed and working
- [ ] All default passwords changed
- [ ] APP_DEBUG=false in .env
- [ ] SESSION_SECURE=true in .env
- [ ] Database backups configured and tested
- [ ] File backups configured and tested
- [ ] Error logging configured
- [ ] Monitoring configured
- [ ] Security headers present
- [ ] Firewall configured
- [ ] File permissions correct
- [ ] All functionality tested
- [ ] Performance tested
- [ ] Security tested
- [ ] Documentation complete
- [ ] Team trained on system

---

**Last Updated**: 2025-10-21
**Version**: 1.0
**Status**: Production Ready
