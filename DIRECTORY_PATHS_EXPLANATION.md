# Directory Paths Explanation

## Path Structure

### Inside Docker Containers (Standard)

- **Working Directory**: `/var/www/html` ✅ **CORRECT**
  - This is the standard web root directory inside containers
  - PHP-FPM containers use `/var/www/html` as their working directory
  - Nginx serves files from `/var/www/html/crm/public` and `/var/www/html/optix2/public`

### On Host Server (Your Choice)

- **Current Location**: `~/optix` (which is `/home/ubuntu/optix`) ✅ **CORRECT**
- **Alternative**: `/opt/optix` or `/var/www/optix` (optional)

## Current Configuration

### Host Server Paths

```
/home/ubuntu/optix/                    (or wherever you cloned it)
├── CRM/optical-crm/
├── optix2/
├── nginx/
└── docker-compose.yml
```

### Inside Containers

```
/var/www/html/                         (CRM PHP container)
├── vendor/
├── app/
├── public/
└── ...

/var/www/html/                         (Optix2 PHP container)
├── app/
├── public/
└── ...

/var/www/html/                         (Nginx container)
├── crm/
│   ├── public/                        (mapped from CRM/optical-crm/public)
│   └── ...
└── optix2/
    ├── public/                        (mapped from optix2/public)
    └── ...
```

## Docker Volume Mappings

### CRM PHP Container

```yaml
volumes:
  - ./CRM/optical-crm:/var/www/html
```

**Host**: `~/optix/CRM/optical-crm` → **Container**: `/var/www/html`

### Optix2 PHP Container

```yaml
volumes:
  - ./optix2:/var/www/html
```

**Host**: `~/optix/optix2` → **Container**: `/var/www/html`

### Nginx Container

```yaml
volumes:
  - ./CRM/optical-crm/public:/var/www/html/crm/public
  - ./optix2/public:/var/www/html/optix2/public
```

## Summary

✅ **Inside containers**: `/var/www/html` is **CORRECT** (standard web root)  
✅ **On host server**: `~/optix` or `/opt/optix` or `/var/www/optix` - all work fine

**The paths are correct!** `/var/www/html` is the standard location inside Docker containers for web applications.

## If You Want to Move Project on Host

If you prefer `/var/www/optix` on the host:

```bash
# Move project
sudo mv ~/optix /var/www/optix
cd /var/www/optix

# Ensure permissions
sudo chown -R $USER:$USER /var/www/optix

# Restart containers
sudo docker compose down
sudo docker compose up -d
```

But `~/optix` works perfectly fine! The container paths (`/var/www/html`) are what matter.
