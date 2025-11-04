# Quick Server Deployment Checklist

## Pre-Deployment

- [ ] Server with Ubuntu/Debian/CentOS
- [ ] SSH access with sudo privileges
- [ ] Domain names configured (optional)

## Step 1: Install Docker

```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER
```

## Step 2: Upload Project

```bash
cd /opt
git clone <repo-url> optix
# OR upload via SCP/SFTP
```

## Step 3: Create .env File

```bash
cd /opt/optix
cp ENV_SETUP_GUIDE.md .env
nano .env  # Fill in all passwords
```

## Step 4: Build & Start

```bash
docker-compose build
docker-compose up -d
```

## Step 5: Initialize Laravel

```bash
docker-compose exec crm_php php artisan key:generate
docker-compose exec crm_php php artisan migrate --force
docker-compose exec crm_php chmod -R 775 storage bootstrap/cache
```

## Step 6: Configure Firewall

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

## Step 7: Verify

```bash
docker-compose ps
curl http://your-server-ip
```

## Done! ✅

**Access URLs:**

- CRM: `http://your-server-ip`
- Optix2: `http://optix2.yourdomain.com` (if configured)

For detailed instructions, see `SERVER_DEPLOYMENT_STEPS.md`
