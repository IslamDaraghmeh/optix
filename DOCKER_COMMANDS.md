# Docker Deployment Commands - Quick Reference

## Initial Setup (First Time on Server)

```bash
# 1. Navigate to project root
cd /opt/optix  # or wherever your project is located

# 2. Build and start all services
docker-compose up -d --build

# 3. Generate Laravel application key
docker-compose exec crm_php php artisan key:generate

# 4. Run Laravel migrations (auto-runs, but can manually run)
docker-compose exec crm_php php artisan migrate --force

# 5. Set proper permissions
docker-compose exec crm_php chown -R www-data:www-data storage bootstrap/cache
docker-compose exec crm_php chmod -R 775 storage bootstrap/cache
docker-compose exec optix2_php chown -R www-data:www-data storage
docker-compose exec optix2_php chmod -R 775 storage

# 6. Cache Laravel configuration
docker-compose exec crm_php php artisan config:cache
docker-compose exec crm_php php artisan route:cache
docker-compose exec crm_php php artisan view:cache
```

## Daily Operations

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart all services
docker-compose restart

# Restart specific service
docker-compose restart crm_php
docker-compose restart optix2_php
docker-compose restart nginx

# View status
docker-compose ps

# View logs (all services)
docker-compose logs -f

# View logs (specific service)
docker-compose logs -f crm_php
docker-compose logs -f optix2_php
docker-compose logs -f nginx
docker-compose logs -f mysql_crm
docker-compose logs -f mysql_optix2
```

## Laravel Commands

```bash
# Artisan commands
docker-compose exec crm_php php artisan <command>

# Examples:
docker-compose exec crm_php php artisan migrate
docker-compose exec crm_php php artisan cache:clear
docker-compose exec crm_php php artisan config:clear
docker-compose exec crm_php php artisan route:clear
docker-compose exec crm_php php artisan view:clear

# Composer commands
docker-compose exec crm_php composer install
docker-compose exec crm_php composer update
```

## Database Operations

```bash
# Backup CRM database
docker-compose exec mysql_crm mysqldump -u crm_user -pcrm_password optical_crm > crm_backup_$(date +%Y%m%d).sql

# Backup Optix2 database
docker-compose exec mysql_optix2 mysqldump -u optix_user -poptix_password optix_clinic > optix2_backup_$(date +%Y%m%d).sql

# Restore CRM database
cat crm_backup.sql | docker-compose exec -T mysql_crm mysql -u crm_user -pcrm_password optical_crm

# Restore Optix2 database
cat optix2_backup.sql | docker-compose exec -T mysql_optix2 mysql -u optix_user -poptix_password optix_clinic

# Access MySQL CLI
docker-compose exec mysql_crm mysql -u crm_user -pcrm_password optical_crm
docker-compose exec mysql_optix2 mysql -u optix_user -poptix_password optix_clinic
```

## Container Shell Access

```bash
# Laravel CRM container
docker-compose exec crm_php bash

# Optix2 container
docker-compose exec optix2_php bash

# Nginx container
docker-compose exec nginx sh

# MySQL containers
docker-compose exec mysql_crm bash
docker-compose exec mysql_optix2 bash
```

## Troubleshooting

```bash
# Check container health
docker-compose ps

# Check specific container logs
docker-compose logs --tail=100 crm_php

# Restart specific service
docker-compose restart crm_php

# Rebuild specific service
docker-compose build crm_php
docker-compose up -d crm_php

# Test database connections
docker-compose exec mysql_crm mysql -u crm_user -pcrm_password -e "SELECT 1"
docker-compose exec mysql_optix2 mysql -u optix_user -poptix_password -e "SELECT 1"

# Test Nginx configuration
docker-compose exec nginx nginx -t

# Reload Nginx
docker-compose exec nginx nginx -s reload
```

## Clean Up

```bash
# Stop and remove containers
docker-compose down

# Stop and remove containers + volumes (WARNING: deletes databases)
docker-compose down -v

# Remove all unused Docker resources
docker system prune -a

# View disk usage
docker system df
```

## Access URLs

- **Laravel CRM**: http://your-server-ip or http://crm.localhost (configure hosts file)
- **Optix2**: http://your-server-ip (access via different URL/path) or http://optix2.localhost

## Important Notes

1. **Change default passwords** in `docker-compose.yml` before production
2. **Configure domain names** in `nginx/conf.d/default.conf` for production
3. **Set up SSL** for production (Let's Encrypt recommended)
4. **Backup databases regularly** (see backup commands above)
5. **Monitor logs** for errors: `docker-compose logs -f`
