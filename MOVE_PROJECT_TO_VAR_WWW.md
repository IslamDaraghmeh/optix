# Moving Project to /var/www/optix

## Issue

`/var/www/optix` directory doesn't exist yet.

## Solution

### Option 1: Create Directory First, Then Move

```bash
# Create the target directory
sudo mkdir -p /var/www/optix

# Move the project
sudo mv /home/ubuntu/optix /var/www/optix

# Set ownership
sudo chown -R $USER:$USER /var/www/optix

# Navigate to new location
cd /var/www/optix

# Restart containers
sudo docker compose down
sudo docker compose up -d
```

### Option 2: Move in One Command (Simpler)

```bash
# Move and create parent directory automatically
sudo mv /home/ubuntu/optix /var/www/

# Set ownership
sudo chown -R $USER:$USER /var/www/optix

# Navigate to new location
cd /var/www/optix

# Restart containers
sudo docker compose down
sudo docker compose up -d
```

### Option 3: Copy Instead of Move (Safer)

```bash
# Copy instead of move (keeps original)
sudo cp -r /home/ubuntu/optix /var/www/optix

# Set ownership
sudo chown -R $USER:$USER /var/www/optix

# Navigate to new location
cd /var/www/optix

# Restart containers
sudo docker compose down
sudo docker compose up -d

# Once verified, you can delete original:
# rm -rf /home/ubuntu/optix
```

## After Moving

```bash
# Verify files are there
ls -la /var/www/optix

# Check docker-compose.yml
cd /var/www/optix
cat docker-compose.yml | head -10

# Restart services
sudo docker compose down
sudo docker compose up -d

# Verify services
sudo docker compose ps
```

## Note

**You don't need to move it!** `~/optix` works perfectly fine. Moving to `/var/www/optix` is optional and only for organizational purposes.
