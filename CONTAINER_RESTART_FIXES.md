# Container Restart Issues - Fixed

## Issues Found

1. **Nginx restarting**: Duplicate `server_tokens` directive

   - Both `default.conf` and `production.conf` exist
   - Both have `server_tokens off;`
   - Nginx loads all .conf files, causing conflict

2. **CRM PHP restarting**: Missing vendor/autoload.php
   - Entrypoint script runs artisan commands before vendor exists
   - Vendor directory might not be properly copied

## Fixes Applied

### Fix 1: Nginx Configuration

- Commented out directives in `production.conf` that conflict with `default.conf`
- `production.conf` is now a template for future SSL setup
- Only `default.conf` is active

### Fix 2: CRM PHP Entrypoint

- Added check for vendor/autoload.php before running artisan commands
- If missing, runs composer install automatically
- Prevents crashes due to missing vendor directory

## Restart Services

```bash
# Restart containers
sudo docker compose restart nginx crm_php

# Or full restart
sudo docker compose down
sudo docker compose up -d

# Check status
sudo docker compose ps

# Check logs
sudo docker compose logs -f crm_php
sudo docker compose logs -f nginx
```

## Verify Fix

```bash
# Nginx should start without errors
sudo docker compose logs nginx | grep -i error

# CRM PHP should start successfully
sudo docker compose logs crm_php | tail -20
```

Both containers should now start successfully! ✅
