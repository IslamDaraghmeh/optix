# Container Restart Troubleshooting

## Current Status

- ✅ optix2_php - Running
- ✅ MySQL containers - Healthy
- ❌ optix_crm_php - Restarting (255)
- ❌ optix_nginx - Restarting (1)

## Diagnostic Commands

Run these to see the actual errors:

```bash
# Check CRM PHP logs
sudo docker compose logs crm_php | tail -50

# Check Nginx logs
sudo docker compose logs nginx | tail -50

# Check last 20 lines of each
sudo docker compose logs --tail=20 crm_php
sudo docker compose logs --tail=20 nginx
```

## Common Causes & Quick Fixes

### CRM PHP Issue (Exit Code 255)

**Most likely**: Vendor directory issue or entrypoint script error

**Fix:**

```bash
# Check if vendor exists
sudo docker compose exec crm_php ls -la /var/www/html/vendor/

# If missing, rebuild container
sudo docker compose build --no-cache crm_php
sudo docker compose up -d crm_php

# Or manually run composer install
sudo docker compose exec crm_php composer install
```

### Nginx Issue (Exit Code 1)

**Most likely**: Configuration syntax error

**Fix:**

```bash
# Test nginx configuration
sudo docker compose exec nginx nginx -t

# Check for duplicate directives
sudo docker compose exec nginx nginx -T | grep -i "server_tokens\|duplicate"

# If production.conf is causing issues, rename it
sudo mv nginx/conf.d/production.conf nginx/conf.d/production.conf.example
sudo docker compose restart nginx
```

## Quick Recovery Steps

```bash
# 1. Check logs first
sudo docker compose logs crm_php | tail -30
sudo docker compose logs nginx | tail -30

# 2. Rebuild CRM PHP
sudo docker compose build --no-cache crm_php

# 3. Temporarily disable production.conf if it exists
sudo mv nginx/conf.d/production.conf nginx/conf.d/production.conf.disabled 2>/dev/null || true

# 4. Restart services
sudo docker compose restart crm_php nginx

# 5. Check status
sudo docker compose ps

# 6. If still failing, check logs again
sudo docker compose logs -f crm_php nginx
```

## Most Likely Fix

Run these commands:

```bash
# 1. Temporarily disable production.conf
sudo mv nginx/conf.d/production.conf nginx/conf.d/production.conf.disabled 2>/dev/null || true

# 2. Rebuild CRM PHP
sudo docker compose build crm_php

# 3. Restart all
sudo docker compose down
sudo docker compose up -d

# 4. Check status
sudo docker compose ps

# 5. Watch logs
sudo docker compose logs -f crm_php
```

Share the log output if you want me to help diagnose the specific errors.
