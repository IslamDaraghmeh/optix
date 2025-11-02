# Production Readiness Checklist

## ❌ CRITICAL ISSUES (Must Fix Before Production)

### 🔴 Security Issues

- [ ] **CHANGE ALL DEFAULT PASSWORDS** in `docker-compose.yml`:
  - `rootpassword` → Strong MySQL root password (20+ characters)
  - `crm_password` → Strong CRM database password
  - `optix_password` → Strong Optix2 database password
- [ ] **Generate Laravel APP_KEY**:

  ```bash
  docker-compose exec crm_php php artisan key:generate
  ```

  Then update `docker-compose.yml` with the generated key or use environment file

- [ ] **Remove Database Port Exposures** (if not needed):

  - Currently exposing MySQL ports 3306 and 3307 to host
  - Remove port mappings if databases should only be accessible internally
  - Or configure firewall to only allow specific IPs

- [ ] **Configure SSL/HTTPS**:

  - Set up SSL certificates (Let's Encrypt recommended)
  - Update nginx configuration with SSL
  - Update APP_URL to use https://
  - Force HTTPS redirects

- [ ] **Add Security Headers to Nginx**:

  - X-Frame-Options
  - X-Content-Type-Options
  - Strict-Transport-Security
  - Content-Security-Policy
  - X-XSS-Protection

- [ ] **Enable Rate Limiting**:
  - Configure nginx rate limiting
  - Protect login endpoints
  - Protect API endpoints

### 🔴 Configuration Issues

- [ ] **Update Domain Names**:

  - Change `crm.localhost` to your actual domain
  - Change `optix2.localhost` to your actual domain
  - Update `nginx/conf.d/default.conf`
  - Update `APP_URL` in docker-compose.yml

- [ ] **Use Environment File for Sensitive Data**:

  - Create `.env` files for both projects
  - Move sensitive environment variables to `.env` files
  - Use docker-compose env_file directive

- [ ] **Configure PHP for Production**:

  - Set appropriate memory limits
  - Configure opcache properly
  - Disable dangerous PHP functions
  - Set proper error reporting

- [ ] **Database Security**:
  - Use separate database users with minimal privileges
  - Disable remote MySQL access if not needed
  - Configure MySQL bind address

### 🔴 Missing Production Features

- [ ] **Set Up Automated Backups**:

  - Schedule database backups
  - Backup file uploads
  - Test restore procedures

- [ ] **Configure Log Rotation**:

  - Set up logrotate for application logs
  - Configure nginx log rotation
  - Configure container log limits

- [ ] **Set Resource Limits**:

  - Add CPU and memory limits to containers
  - Configure MySQL max connections
  - Set PHP-FPM process limits

- [ ] **Firewall Configuration**:

  - Allow only ports 80, 443, and SSH
  - Block database ports from external access
  - Configure fail2ban for SSH

- [ ] **Monitoring Setup**:
  - Set up container health monitoring
  - Configure uptime monitoring
  - Set up alerting for failures

## ⚠️ IMPORTANT: Before Going Live

### Step 1: Create Production Environment Files

```bash
# Create .env files for sensitive data
# Move passwords and keys out of docker-compose.yml
```

### Step 2: Update docker-compose.yml

- Use environment files instead of inline variables
- Remove or secure database port mappings
- Add resource limits
- Update domain names

### Step 3: SSL Certificate Setup

```bash
# Use Let's Encrypt with Certbot
# Update nginx configuration with SSL
# Force HTTPS redirects
```

### Step 4: Security Hardening

```bash
# Configure firewall
# Set up fail2ban
# Review and update all passwords
# Enable security headers
```

## 📊 Current Status

| Category             | Status                     | Priority |
| -------------------- | -------------------------- | -------- |
| Default Passwords    | ❌ Must Change             | CRITICAL |
| SSL/HTTPS            | ❌ Not Configured          | CRITICAL |
| Laravel APP_KEY      | ❌ Empty                   | CRITICAL |
| Domain Configuration | ❌ localhost               | HIGH     |
| Security Headers     | ❌ Missing                 | HIGH     |
| Rate Limiting        | ❌ Missing                 | HIGH     |
| Backups              | ❌ Not Automated           | MEDIUM   |
| Monitoring           | ❌ Not Configured          | MEDIUM   |
| Resource Limits      | ✅ Provided in prod file   | MEDIUM   |
| Log Rotation         | ✅ Configured in prod file | LOW      |

## 📁 Production Files Created

The following production-ready files have been created:

1. **docker-compose.prod.yml** - Production configuration with:

   - Environment file support
   - Resource limits
   - Log rotation
   - Database ports not exposed externally
   - Security improvements

2. **nginx/conf.d/production.conf** - Production Nginx configuration with:

   - SSL/HTTPS configuration
   - Security headers
   - Rate limiting
   - HSTS
   - Proper logging

3. **.env.production.example** - Template for production environment variables

## ✅ What's Already Good

- ✅ APP_DEBUG=false is set
- ✅ APP_ENV=production is set
- ✅ Separate databases for each application
- ✅ Health checks configured
- ✅ Restart policies set
- ✅ Persistent volumes configured
- ✅ Proper network isolation

## 🚀 Recommended Next Steps

1. **Create production docker-compose.prod.yml** with all fixes
2. **Set up SSL certificates**
3. **Change all passwords**
4. **Configure environment files**
5. **Add security headers to nginx**
6. **Set up automated backups**
7. **Configure monitoring**
8. **Test in staging environment first**

---

**⚠️ DO NOT deploy to production until all CRITICAL issues are resolved!**
