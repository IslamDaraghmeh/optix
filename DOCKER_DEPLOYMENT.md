# Docker Deployment Guide for Optix Projects

This guide provides complete instructions for deploying both the Laravel CRM and Optix2 projects using Docker.

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- At least 2GB RAM available for containers
- At least 10GB free disk space

## Project Structure

The Docker setup includes:

1. **CRM/optical-crm** - Laravel 9 application (PHP 8.1)
2. **optix2** - PHP 8.1 application
3. **MySQL 8.0** - Two separate database instances:
   - `mysql_crm` - For Laravel CRM (port 3307)
   - `mysql_optix2` - For Optix2 (port 3306)
4. **Nginx** - Web server (port 80)

## Quick Start Commands

### 1. Initial Setup (First Time)

```bash
# Navigate to project root
cd /path/to/optix

# Build and start all services
docker-compose up -d --build

# Generate Laravel application key (if not set)
docker-compose exec crm_php php artisan key:generate

# The Laravel entrypoint will automatically run migrations
# Check Laravel logs if needed:
docker-compose logs crm_php
```

### 2. Start Services (After Initial Setup)

```bash
docker-compose up -d
```

### 3. Stop Services

```bash
docker-compose down
```

### 4. Stop Services and Remove Volumes (Complete Cleanup)

```bash
docker-compose down -v
```

## Server Deployment Commands

### Step 1: Prepare Server

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify installations
docker --version
docker-compose --version
```

### Step 2: Clone/Upload Project

```bash
# If using Git
git clone <repository-url> /opt/optix
cd /opt/optix

# OR if uploading files manually, ensure all files are in /opt/optix
```

### Step 3: Configure Environment Variables (Optional)

Edit `docker-compose.yml` to change:

- Database passwords
- Database names
- Port mappings
- Any other environment variables

### Step 4: Build and Start

```bash
# Build images (this may take 5-10 minutes first time)
docker-compose build

# Start all services
docker-compose up -d

# Check service status
docker-compose ps

# View logs
docker-compose logs -f
```

### Step 5: Generate Laravel Key and Run Setup

```bash
# Generate application key for Laravel
docker-compose exec crm_php php artisan key:generate

# Run Laravel migrations (should auto-run on first start)
docker-compose exec crm_php php artisan migrate --force

# Clear and cache Laravel config
docker-compose exec crm_php php artisan config:cache
docker-compose exec crm_php php artisan route:cache
docker-compose exec crm_php php artisan view:cache
```

### Step 6: Configure Nginx (For Production)

Edit `/etc/hosts` on your local machine or configure DNS:

- `crm.localhost` or `your-domain.com` → Your server IP
- `optix2.localhost` or `optix2.your-domain.com` → Your server IP

For production, update `nginx/conf.d/default.conf` with your actual domain names.

## Common Commands

### View Logs

```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f crm_php
docker-compose logs -f optix2_php
docker-compose logs -f nginx
docker-compose logs -f mysql_crm
docker-compose logs -f mysql_optix2
```

### Execute Commands in Containers

```bash
# Laravel CRM container
docker-compose exec crm_php php artisan <command>
docker-compose exec crm_php composer install
docker-compose exec crm_php bash

# Optix2 container
docker-compose exec optix2_php bash
docker-compose exec optix2_php composer install

# MySQL containers
docker-compose exec mysql_crm mysql -u crm_user -pcrm_password optical_crm
docker-compose exec mysql_optix2 mysql -u optix_user -poptix_password optix_clinic
```

### Database Operations

```bash
# Backup CRM database
docker-compose exec mysql_crm mysqldump -u crm_user -pcrm_password optical_crm > crm_backup.sql

# Backup Optix2 database
docker-compose exec mysql_optix2 mysqldump -u optix_user -poptix_password optix_clinic > optix2_backup.sql

# Restore CRM database
cat crm_backup.sql | docker-compose exec -T mysql_crm mysql -u crm_user -pcrm_password optical_crm

# Restore Optix2 database
cat optix2_backup.sql | docker-compose exec -T mysql_optix2 mysql -u optix_user -poptix_password optix_clinic
```

### Rebuild Services

```bash
# Rebuild specific service
docker-compose build crm_php
docker-compose build optix2_php

# Rebuild and restart
docker-compose up -d --build crm_php
```

### Access Services

- **Laravel CRM**: http://localhost (or your server IP)
- **Optix2**: http://optix2.localhost (configure hostname or use IP)
- **MySQL CRM**: localhost:3307
- **MySQL Optix2**: localhost:3306

## Troubleshooting

### Check Container Status

```bash
docker-compose ps
```

All containers should show "Up" status. If any show "Restarting" or "Exited", check logs.

### Fix Permission Issues

```bash
# Fix Laravel storage permissions
docker-compose exec crm_php chown -R www-data:www-data storage bootstrap/cache
docker-compose exec crm_php chmod -R 775 storage bootstrap/cache

# Fix Optix2 storage permissions
docker-compose exec optix2_php chown -R www-data:www-data storage
docker-compose exec optix2_php chmod -R 775 storage
```

### Clear Laravel Caches

```bash
docker-compose exec crm_php php artisan cache:clear
docker-compose exec crm_php php artisan config:clear
docker-compose exec crm_php php artisan route:clear
docker-compose exec crm_php php artisan view:clear
```

### Database Connection Issues

```bash
# Test CRM database connection
docker-compose exec mysql_crm mysql -u crm_user -pcrm_password -e "SELECT 1"

# Test Optix2 database connection
docker-compose exec mysql_optix2 mysql -u optix_user -poptix_password -e "SELECT 1"

# Check if databases exist
docker-compose exec mysql_crm mysql -u root -prootpassword -e "SHOW DATABASES;"
docker-compose exec mysql_optix2 mysql -u root -prootpassword -e "SHOW DATABASES;"
```

### Nginx Configuration Issues

```bash
# Test nginx configuration
docker-compose exec nginx nginx -t

# Reload nginx
docker-compose exec nginx nginx -s reload

# Restart nginx
docker-compose restart nginx
```

### View Container Resources

```bash
# Container stats
docker stats

# Inspect specific container
docker inspect optix_crm_php
docker inspect optix_nginx
```

## Production Checklist

- [ ] Change all default passwords in `docker-compose.yml`
- [ ] Set `APP_DEBUG=false` in environment variables
- [ ] Configure proper domain names in `nginx/conf.d/default.conf`
- [ ] Set up SSL certificates (Let's Encrypt recommended)
- [ ] Configure firewall rules (only allow ports 80, 443, and SSH)
- [ ] Set up automated backups for databases
- [ ] Configure log rotation
- [ ] Set up monitoring (optional)
- [ ] Enable Docker auto-start on system boot

## Production Nginx SSL Configuration

For production with SSL, update `nginx/conf.d/default.conf`:

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;

    # ... rest of configuration
}

server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

## Maintenance Commands

```bash
# Update all images
docker-compose pull
docker-compose up -d

# Clean up unused Docker resources
docker system prune -a

# View disk usage
docker system df

# Remove old containers and images
docker container prune
docker image prune -a
```

## Default Credentials

⚠️ **IMPORTANT: Change these in production!**

### MySQL Databases

**CRM Database:**

- Host: mysql_crm (inside Docker network)
- Port: 3307 (from host)
- Database: optical_crm
- User: crm_user
- Password: crm_password

**Optix2 Database:**

- Host: mysql_optix2 (inside Docker network)
- Port: 3306 (from host)
- Database: optix_clinic
- User: optix_user
- Password: optix_password

### Optix2 Default Users

See `optix2/database/seeds/002_users.sql` for default user credentials.

## Support

For issues, check:

1. Container logs: `docker-compose logs <service_name>`
2. Container status: `docker-compose ps`
3. Database connectivity: Test with MySQL commands above
4. File permissions: Ensure storage directories are writable
