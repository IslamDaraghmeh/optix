# Quick Fix Commands - Run These Now

## Fix 1: Rename production.conf (Nginx Issue)

```bash
cd /var/www/optix

# Rename production.conf to disable it
sudo mv nginx/conf.d/production.conf nginx/conf.d/production.conf.disabled

# Restart nginx
sudo docker compose restart nginx
```

## Fix 2: Install Composer Dependencies (CRM PHP Issue)

The vendor directory is missing because volume mounts overwrite the built image. Install dependencies on host:

```bash
cd /var/www/optix/CRM/optical-crm

# Install composer dependencies
composer install --no-dev --optimize-autoloader

# If composer not installed on host, use Docker:
sudo docker compose run --rm crm_php composer install --no-dev --optimize-autoloader
```

## Fix 3: Rebuild and Restart

```bash
cd /var/www/optix

# Rebuild CRM PHP (with updated entrypoint script)
sudo docker compose build crm_php

# Restart all services
sudo docker compose down
sudo docker compose up -d

# Check status
sudo docker compose ps

# Watch logs
sudo docker compose logs -f crm_php nginx
```

## Alternative: Quick Fix Using Docker

If composer is not installed on host:

```bash
cd /var/www/optix

# Stop CRM PHP
sudo docker compose stop crm_php

# Run composer install in temporary container
sudo docker compose run --rm -v $(pwd)/CRM/optical-crm:/app crm_php composer install --no-dev --optimize-autoloader -d /app

# Rename production.conf
sudo mv nginx/conf.d/production.conf nginx/conf.d/production.conf.disabled

# Restart all
sudo docker compose up -d
```

## Most Important: Do These Two Things

```bash
# 1. Disable production.conf
sudo mv nginx/conf.d/production.conf nginx/conf.d/production.conf.disabled

# 2. Install vendor dependencies
cd /var/www/optix/CRM/optical-crm
sudo docker compose run --rm crm_php composer install --no-dev --optimize-autoloader

# 3. Restart
cd /var/www/optix
sudo docker compose restart crm_php nginx
```

