# Quick Server Setup - Based on Your Current Status

## ✅ Configuration Validated

Your `docker compose config` shows the configuration is **valid**!

## ⚠️ Important: Update Passwords

I notice passwords are still set to default values. Before building, create `.env` file:

```bash
cd ~/optix

# Create .env file
cat > .env << 'EOF'
# MySQL Root Passwords (REQUIRED - Use strong passwords)
MYSQL_ROOT_PASSWORD_OPTIX2=optix_admin
MYSQL_ROOT_PASSWORD_CRM=optix_admin_2025

# Optix2 Database Configuration
DB_DATABASE_OPTIX2=optix_clinic
DB_USERNAME_OPTIX2=optix_user
DB_PASSWORD_OPTIX2=YOUR_STRONG_PASSWORD_HERE

# CRM Database Configuration
DB_DATABASE_CRM=optical_crm
DB_USERNAME_CRM=crm_user
DB_PASSWORD_CRM=YOUR_STRONG_PASSWORD_HERE

# Laravel Application Configuration
APP_NAME=OpticalCRM
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://13.49.159.175
EOF

# Generate strong passwords
openssl rand -base64 32

# Edit .env and replace YOUR_STRONG_PASSWORD_HERE with generated passwords
nano .env
```

## Next Steps

### 1. Create Required Directories

```bash
mkdir -p nginx/ssl nginx/logs
```

### 2. Build Images

```bash
sudo docker compose build
```

### 3. Start Services

```bash
sudo docker compose up -d
```

### 4. Check Status

```bash
sudo docker compose ps
sudo docker compose logs -f
```

### 5. Initialize Laravel

```bash
sudo docker compose exec crm_php php artisan key:generate
sudo docker compose exec crm_php php artisan migrate --force
sudo docker compose exec crm_php chmod -R 775 storage bootstrap/cache
```

## Notes

- ✅ Using `sudo docker compose` (newer compose plugin) - this is correct!
- ✅ Configuration is valid
- ✅ Removed obsolete `version` field from docker-compose.yml
- ⚠️ Remember to set strong passwords in `.env` file
- ⚠️ Make sure nginx directories exist before starting

## Access

After starting, access at:

- **CRM**: http://13.49.159.175
- **Optix2**: http://13.49.159.175 (if configured)

## Troubleshooting

If build fails:

```bash
# Clean rebuild
sudo docker compose build --no-cache

# Check logs
sudo docker compose logs -f
```

If services don't start:

```bash
# Check status
sudo docker compose ps

# View logs
sudo docker compose logs crm_php
sudo docker compose logs optix2_php
sudo docker compose logs nginx
```
