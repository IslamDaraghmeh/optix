# Server Deployment Steps - Complete Guide

## Prerequisites

- **Server**: Ubuntu 20.04+ / Debian 11+ / CentOS 8+ (recommended)
- **RAM**: Minimum 2GB (4GB+ recommended)
- **Disk Space**: Minimum 10GB free
- **Network**: Internet connection for package downloads
- **Access**: SSH access with sudo privileges

---

## Step 1: Connect to Server

```bash
# SSH into your server
ssh user@your-server-ip

# Update system packages
sudo apt update && sudo apt upgrade -y
```

---

## Step 2: Install Docker

```bash
# Remove old Docker versions if any
sudo apt remove docker docker-engine docker.io containerd runc -y

# Install required packages
sudo apt install -y apt-transport-https ca-certificates curl gnupg lsb-release

# Add Docker's official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Add Docker repository
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker Engine
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Verify Docker installation
docker --version
docker compose version

# Add your user to docker group (to run without sudo)
sudo usermod -aG docker $USER

# Log out and log back in for group changes to take effect
# Or run: newgrp docker
```

**Alternative: Install Docker Compose separately (if needed)**

```bash
# Download Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose

# Make executable
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker-compose --version
```

---

## Step 3: Upload Project Files

### Option A: Using Git (Recommended)

```bash
# Install Git if not installed
sudo apt install -y git

# Clone your repository
cd /opt
sudo git clone <your-repository-url> optix
cd optix

# Set ownership
sudo chown -R $USER:$USER /opt/optix
```

### Option B: Using SCP/SFTP

```bash
# On your local machine
scp -r /path/to/optix user@your-server-ip:/opt/

# Or use SFTP client (FileZilla, WinSCP, etc.)
# Upload entire optix directory to /opt/optix
```

### Option C: Direct Upload

```bash
# Create directory
sudo mkdir -p /opt/optix
cd /opt/optix

# Upload files via your preferred method
# Then set ownership
sudo chown -R $USER:$USER /opt/optix
```

---

## Step 4: Create Environment File

```bash
cd /opt/optix

# Create .env file
cat > .env << 'EOF'
# MySQL Root Passwords (REQUIRED - Use strong passwords, minimum 20 characters)
# Generate strong passwords: openssl rand -base64 32
MYSQL_ROOT_PASSWORD_OPTIX2=CHANGE_THIS_STRONG_PASSWORD_20_CHARS_MIN
MYSQL_ROOT_PASSWORD_CRM=CHANGE_THIS_STRONG_PASSWORD_20_CHARS_MIN

# Optix2 Database Configuration
DB_DATABASE_OPTIX2=optix_clinic
DB_USERNAME_OPTIX2=optix_user
DB_PASSWORD_OPTIX2=CHANGE_THIS_STRONG_PASSWORD_20_CHARS_MIN

# CRM Database Configuration
DB_DATABASE_CRM=optical_crm
DB_USERNAME_CRM=crm_user
DB_PASSWORD_CRM=CHANGE_THIS_STRONG_PASSWORD_20_CHARS_MIN

# Laravel Application Configuration
APP_NAME=OpticalCRM
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://your-server-ip
EOF

# Edit .env file and replace all CHANGE_THIS_* values with strong passwords
nano .env

# Generate strong passwords (run this and copy the output)
openssl rand -base64 32
```

**Important**: Replace all `CHANGE_THIS_*` values with strong passwords before proceeding!

---

## Step 5: Create Required Directories

```bash
cd /opt/optix

# Create nginx SSL directory (for future SSL certificates)
mkdir -p nginx/ssl

# Create nginx logs directory
mkdir -p nginx/logs

# Ensure proper permissions
chmod -R 755 nginx/
```

---

## Step 6: Build Docker Images

```bash
cd /opt/optix

# Build all images (this may take 5-10 minutes first time)
docker-compose build

# Check if build was successful
docker-compose config
```

---

## Step 7: Start Services

```bash
cd /opt/optix

# Start all services in detached mode
docker-compose up -d

# Check service status
docker-compose ps

# View logs (to verify everything is starting correctly)
docker-compose logs -f
```

**Wait 1-2 minutes** for services to fully start, then check status again.

---

## Step 8: Initialize Laravel CRM

```bash
# Generate Laravel application key (if not auto-generated)
docker-compose exec crm_php php artisan key:generate

# Run migrations (should auto-run, but verify)
docker-compose exec crm_php php artisan migrate --force

# Set proper permissions
docker-compose exec crm_php chown -R www-data:www-data storage bootstrap/cache
docker-compose exec crm_php chmod -R 775 storage bootstrap/cache

# Cache configuration for production
docker-compose exec crm_php php artisan config:cache
docker-compose exec crm_php php artisan route:cache
docker-compose exec crm_php php artisan view:cache
```

---

## Step 9: Verify Services

```bash
# Check all containers are running
docker-compose ps

# All should show "Up" status

# Check logs for any errors
docker-compose logs crm_php
docker-compose logs optix2_php
docker-compose logs nginx
docker-compose logs mysql_crm
docker-compose logs mysql_optix2

# Test database connections
docker-compose exec mysql_crm mysql -u crm_user -p -e "SELECT 1"
docker-compose exec mysql_optix2 mysql -u optix_user -p -e "SELECT 1"

# Test Nginx configuration
docker-compose exec nginx nginx -t
```

---

## Step 10: Configure Firewall

```bash
# Install UFW firewall if not installed
sudo apt install -y ufw

# Allow SSH (important - do this first!)
sudo ufw allow ssh

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check firewall status
sudo ufw status
```

---

## Step 11: Configure Domain Names (Optional)

### Option A: Using DNS

1. Point your domain to server IP:

   - `crm.yourdomain.com` → Your server IP
   - `optix2.yourdomain.com` → Your server IP

2. Update nginx configuration:

```bash
cd /opt/optix
nano nginx/conf.d/default.conf
```

Change:

- `server_name crm.localhost localhost;` → `server_name crm.yourdomain.com;`
- `server_name optix2.localhost;` → `server_name optix2.yourdomain.com;`

3. Reload nginx:

```bash
docker-compose exec nginx nginx -s reload
```

### Option B: Using Server IP

If using IP address directly:

- Access CRM at: `http://your-server-ip`
- Access Optix2 at: `http://your-server-ip` (configure different path or port)

---

## Step 12: Set Up SSL Certificates (Production)

### Using Let's Encrypt (Recommended)

```bash
# Install Certbot
sudo apt install -y certbot

# Stop nginx temporarily
docker-compose stop nginx

# Obtain certificates (replace with your domain)
sudo certbot certonly --standalone -d crm.yourdomain.com -d optix2.yourdomain.com

# Certificates will be in:
# /etc/letsencrypt/live/crm.yourdomain.com/fullchain.pem
# /etc/letsencrypt/live/crm.yourdomain.com/privkey.pem

# Copy certificates to nginx/ssl directory
sudo cp /etc/letsencrypt/live/crm.yourdomain.com/fullchain.pem nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/crm.yourdomain.com/privkey.pem nginx/ssl/key.pem

# Set permissions
sudo chmod 644 nginx/ssl/cert.pem
sudo chmod 600 nginx/ssl/key.pem

# Update nginx configuration to use SSL (see nginx/conf.d/production.conf)
# Then restart nginx
docker-compose start nginx
```

---

## Step 13: Access Applications

### Check Access

```bash
# From your local machine or browser
curl http://your-server-ip

# Should see Laravel CRM or Nginx response
```

### Access URLs:

- **Laravel CRM**: `http://your-server-ip` or `http://crm.yourdomain.com`
- **Optix2**: `http://optix2.yourdomain.com` (if configured)

---

## Step 14: Set Up Auto-Start on Boot

```bash
# Docker should already auto-start, but verify
sudo systemctl enable docker

# Create systemd service for docker-compose (optional)
sudo nano /etc/systemd/system/optix.service
```

Add:

```ini
[Unit]
Description=Optix Docker Compose
Requires=docker.service
After=docker.service

[Service]
Type=oneshot
RemainAfterExit=yes
WorkingDirectory=/opt/optix
ExecStart=/usr/bin/docker compose up -d
ExecStop=/usr/bin/docker compose down
User=your-username

[Install]
WantedBy=multi-user.target
```

Enable:

```bash
sudo systemctl enable optix.service
sudo systemctl start optix.service
```

---

## Step 15: Set Up Backups (Important!)

```bash
cd /opt/optix

# Create backup script
cat > backup.sh << 'EOF'
#!/bin/bash
BACKUP_DIR="/opt/backups/optix"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup CRM database
docker-compose exec -T mysql_crm mysqldump -u crm_user -p"$DB_PASSWORD_CRM" optical_crm | gzip > $BACKUP_DIR/crm_db_$DATE.sql.gz

# Backup Optix2 database
docker-compose exec -T mysql_optix2 mysqldump -u optix_user -p"$DB_PASSWORD_OPTIX2" optix_clinic | gzip > $BACKUP_DIR/optix2_db_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
EOF

chmod +x backup.sh

# Test backup
./backup.sh

# Add to crontab (daily at 2 AM)
crontab -e
# Add: 0 2 * * * /opt/optix/backup.sh >> /opt/optix/backup.log 2>&1
```

---

## Troubleshooting

### Services Not Starting

```bash
# Check container status
docker-compose ps

# View logs
docker-compose logs -f

# Restart services
docker-compose restart

# Rebuild if needed
docker-compose up -d --build
```

### Database Connection Issues

```bash
# Check database containers
docker-compose ps mysql_crm mysql_optix2

# Test connections
docker-compose exec mysql_crm mysql -u crm_user -p -e "SELECT 1"
docker-compose exec mysql_optix2 mysql -u optix_user -p -e "SELECT 1"

# Check environment variables
docker-compose exec crm_php env | grep DB_
```

### Permission Issues

```bash
# Fix Laravel permissions
docker-compose exec crm_php chown -R www-data:www-data storage bootstrap/cache
docker-compose exec crm_php chmod -R 775 storage bootstrap/cache

# Fix Optix2 permissions
docker-compose exec optix2_php chown -R www-data:www-data storage
docker-compose exec optix2_php chmod -R 775 storage
```

### Nginx Issues

```bash
# Test nginx configuration
docker-compose exec nginx nginx -t

# Reload nginx
docker-compose exec nginx nginx -s reload

# Restart nginx
docker-compose restart nginx
```

---

## Quick Reference Commands

```bash
# Navigate to project
cd /opt/optix

# Start services
docker-compose up -d

# Stop services
docker-compose down

# Restart services
docker-compose restart

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f crm_php
docker-compose logs -f optix2_php
docker-compose logs -f nginx

# Check status
docker-compose ps

# Execute commands in containers
docker-compose exec crm_php php artisan <command>
docker-compose exec crm_php bash
docker-compose exec optix2_php bash

# Backup databases
docker-compose exec mysql_crm mysqldump -u crm_user -p optical_crm > backup.sql
docker-compose exec mysql_optix2 mysqldump -u optix_user -p optix_clinic > backup.sql
```

---

## Verification Checklist

- [ ] Docker installed and running
- [ ] Project files uploaded to `/opt/optix`
- [ ] `.env` file created with strong passwords
- [ ] All containers running (`docker-compose ps`)
- [ ] No errors in logs (`docker-compose logs`)
- [ ] Database connections working
- [ ] Nginx routing correctly
- [ ] Laravel APP_KEY generated
- [ ] Laravel migrations run
- [ ] Applications accessible via browser
- [ ] Firewall configured
- [ ] Backups configured (optional but recommended)

---

## Next Steps After Deployment

1. **Change default passwords** (if using seed data)
2. **Set up SSL certificates** for HTTPS
3. **Configure domain names** properly
4. **Set up monitoring** (optional)
5. **Set up automated backups**
6. **Review security settings**
7. **Test all functionality**

---

## Support

If you encounter issues:

1. Check logs: `docker-compose logs -f`
2. Verify `.env` file configuration
3. Check container status: `docker-compose ps`
4. Review firewall settings
5. Verify network connectivity

---

**Your applications should now be running on the server!** 🚀
