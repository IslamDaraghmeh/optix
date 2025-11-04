# MySQL SQL File Error Fix

## Error

```
ERROR: Can't initialize batch_readline - may be the input source is a directory or a block device.
```

## Root Cause

MySQL entrypoint script is trying to execute `/docker-entrypoint-initdb.d/01-schema.sql` but:

- File might be empty
- File might be a directory
- File path might be incorrect
- File permissions issue

## Quick Fix

### Step 1: Verify SQL Files Exist

```bash
cd ~/optix

# Check if files exist
ls -lh optix2/database/schema.sql
ls -lh optix2/database/seeds/*.sql

# Check file sizes (should not be 0)
du -h optix2/database/schema.sql
```

### Step 2: Check File Contents

```bash
# Check first few lines of schema file
head -20 optix2/database/schema.sql

# Verify it's a valid SQL file (not empty)
wc -l optix2/database/schema.sql
```

### Step 3: Fix File Permissions

```bash
# Ensure files are readable
chmod 644 optix2/database/schema.sql
chmod 644 optix2/database/seeds/*.sql

# Ensure directory is readable
chmod 755 optix2/database
chmod 755 optix2/database/seeds
```

### Step 4: Reset and Restart

```bash
# Stop containers
sudo docker compose down

# Remove problematic volume
sudo docker volume rm optix_mysql_optix2_data

# Restart MySQL container
sudo docker compose up -d mysql_optix2

# Watch logs
sudo docker compose logs -f mysql_optix2
```

## Alternative: Skip SQL Files Temporarily

If SQL files are causing issues, you can temporarily disable them:

1. Comment out the volume mounts in docker-compose.yml:

```yaml
# volumes:
#   - ./optix2/database/schema.sql:/docker-entrypoint-initdb.d/01-schema.sql:ro
#   - ./optix2/database/seeds/001_locations.sql:/docker-entrypoint-initdb.d/02-locations.sql:ro
#   - ./optix2/database/seeds/002_users.sql:/docker-entrypoint-initdb.d/03-users.sql:ro
#   - ./optix2/database/seeds/003_sample_data.sql:/docker-entrypoint-initdb.d/04-sample.sql:ro
```

2. Start MySQL without initialization scripts
3. Manually import SQL files later:

```bash
sudo docker compose exec mysql_optix2 mysql -u root -p optix_clinic < /path/to/schema.sql
```

## Most Likely Issue

Based on the error, the SQL file is probably:

- Empty (0 bytes)
- Missing
- Directory instead of file

Check with:

```bash
file optix2/database/schema.sql
ls -la optix2/database/schema.sql
```
