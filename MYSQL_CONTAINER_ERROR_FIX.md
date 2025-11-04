# MySQL Container Error - Troubleshooting

## Issue

`optix_mysql_optix2` container showing Error status

## Quick Diagnosis

```bash
# Check container logs
sudo docker compose logs mysql_optix2

# Check container status
sudo docker compose ps mysql_optix2

# Check if container exists
sudo docker ps -a | grep mysql_optix2
```

## Common Causes & Solutions

### 1. Database Initialization Error

**Check logs:**

```bash
sudo docker compose logs mysql_optix2 | tail -50
```

**Common issues:**

- SQL files not found or have syntax errors
- Database already exists
- Permission issues

**Fix:**

```bash
# Remove the container and volume
sudo docker compose down
sudo docker volume rm optix_mysql_optix2_data

# Check if SQL files exist
ls -la optix2/database/schema.sql
ls -la optix2/database/seeds/

# Recreate
sudo docker compose up -d mysql_optix2
```

### 2. Port Conflict

**Check if port 3306 is already in use:**

```bash
sudo netstat -tulpn | grep 3306
# OR
sudo lsof -i :3306
```

**Fix:**

- Stop any existing MySQL service: `sudo systemctl stop mysql` (if installed)
- Or change port in docker-compose.yml

### 3. Volume Permission Issues

**Fix:**

```bash
# Remove problematic volume
sudo docker compose down
sudo docker volume rm optix_mysql_optix2_data

# Recreate
sudo docker compose up -d mysql_optix2
```

### 4. SQL File Path Issues

**Check:**

```bash
# Verify files exist
ls -la optix2/database/schema.sql
ls -la optix2/database/seeds/*.sql

# Check file permissions
stat optix2/database/schema.sql
```

**Fix:**

- Ensure files are readable
- Check paths in docker-compose.yml match actual file locations

## Step-by-Step Recovery

```bash
# 1. Stop all services
sudo docker compose down

# 2. Check logs for error details
sudo docker compose logs mysql_optix2 > mysql_error.log
cat mysql_error.log

# 3. Remove problematic volume (if needed)
sudo docker volume rm optix_mysql_optix2_data

# 4. Verify SQL files exist and are readable
ls -lh optix2/database/schema.sql
ls -lh optix2/database/seeds/*.sql

# 5. Recreate just the MySQL container
sudo docker compose up -d mysql_optix2

# 6. Watch logs in real-time
sudo docker compose logs -f mysql_optix2
```

## Most Likely Issue

Based on the error, it's probably:

1. **SQL file missing or path incorrect** - Check file paths
2. **Database initialization script error** - Check SQL syntax
3. **Volume already exists with incompatible data** - Remove volume

## Quick Fix Command

```bash
# Full reset (WARNING: deletes data)
sudo docker compose down -v
sudo docker compose up -d mysql_optix2
sudo docker compose logs -f mysql_optix2
```

## After Fix

Once MySQL starts successfully:

```bash
# Start remaining services
sudo docker compose up -d

# Verify all services
sudo docker compose ps
```
