# Quick Fix: MySQL Container Starting Without SQL Files

## Issue Fixed

Commented out SQL file initialization in docker-compose.yml to allow MySQL to start successfully.

## Next Steps

### 1. Restart MySQL Container

```bash
# Stop containers
sudo docker compose down

# Remove the problematic volume (if needed)
sudo docker volume rm optix_mysql_optix2_data

# Start MySQL without SQL initialization
sudo docker compose up -d mysql_optix2

# Check if it starts successfully
sudo docker compose logs -f mysql_optix2
```

### 2. Verify MySQL is Running

```bash
# Check container status
sudo docker compose ps mysql_optix2

# Should show "Healthy" status
```

### 3. Import SQL Files Manually (If Needed)

If you have SQL files and want to import them:

```bash
# Check if SQL files exist
ls -lh optix2/database/schema.sql
ls -lh optix2/database/seeds/*.sql

# If files exist, import them manually
sudo docker compose exec -T mysql_optix2 mysql -u root -poptix_admin optix_clinic < optix2/database/schema.sql

# Import seed files
sudo docker compose exec -T mysql_optix2 mysql -u root -poptix_admin optix_clinic < optix2/database/seeds/001_locations.sql
sudo docker compose exec -T mysql_optix2 mysql -u root -poptix_admin optix_clinic < optix2/database/seeds/002_users.sql
sudo docker compose exec -T mysql_optix2 mysql -u root -poptix_admin optix_clinic < optix2/database/seeds/003_sample_data.sql
```

### 4. Start All Services

Once MySQL is running:

```bash
sudo docker compose up -d

# Check all services
sudo docker compose ps
```

## To Re-enable Auto SQL Import Later

1. Ensure SQL files exist and are valid:

   ```bash
   ls -lh optix2/database/schema.sql
   file optix2/database/schema.sql  # Should show "ASCII text" or "SQL"
   ```

2. Uncomment the volume mounts in docker-compose.yml:

   ```yaml
   volumes:
     - mysql_optix2_data:/var/lib/mysql
     - ./optix2/database/schema.sql:/docker-entrypoint-initdb.d/01-schema.sql:ro
     # ... etc
   ```

3. Remove volume and restart:
   ```bash
   sudo docker compose down
   sudo docker volume rm optix_mysql_optix2_data
   sudo docker compose up -d mysql_optix2
   ```

## Note

MySQL will now start successfully without trying to auto-import SQL files. You can import them manually later if needed, or skip them entirely if optix2 doesn't need them.
