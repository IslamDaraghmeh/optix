# Production Environment Variables Template

Create a `.env.production` file with the following variables:

```bash
# MySQL Root Passwords (CHANGE THESE - Use strong passwords, 20+ characters)
MYSQL_ROOT_PASSWORD_OPTIX2=CHANGE_ME_STRONG_PASSWORD_HERE
MYSQL_ROOT_PASSWORD_CRM=CHANGE_ME_STRONG_PASSWORD_HERE

# Optix2 Database Configuration
DB_DATABASE_OPTIX2=optix_clinic
DB_USERNAME_OPTIX2=optix_user
DB_PASSWORD_OPTIX2=CHANGE_ME_STRONG_PASSWORD_HERE

# CRM Database Configuration
DB_DATABASE_CRM=optical_crm
DB_USERNAME_CRM=crm_user
DB_PASSWORD_CRM=CHANGE_ME_STRONG_PASSWORD_HERE

# Domain Configuration (UPDATE THESE)
CRM_DOMAIN=crm.yourdomain.com
OPTIX2_DOMAIN=optix2.yourdomain.com
```

## Usage

```bash
# Create the file
cp PRODUCTION_ENV_TEMPLATE.md .env.production

# Edit with your values
nano .env.production

# Use with docker-compose
docker-compose -f docker-compose.prod.yml --env-file .env.production up -d
```
