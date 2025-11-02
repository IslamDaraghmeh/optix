# Environment Variables Setup Guide

## Quick Setup

```bash
# Create .env file in project root
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
# APP_KEY will be auto-generated if empty by docker-entrypoint.sh
APP_KEY=
APP_DEBUG=false
# Update with your actual domain
APP_URL=http://localhost
EOF

# Then edit with your actual values
nano .env
```

## Required Variables

### MySQL Root Passwords (CRITICAL - Must Change!)

- `MYSQL_ROOT_PASSWORD_OPTIX2` - Strong password (20+ characters)
- `MYSQL_ROOT_PASSWORD_CRM` - Strong password (20+ characters)

### Optix2 Database

- `DB_DATABASE_OPTIX2` - Default: `optix_clinic`
- `DB_USERNAME_OPTIX2` - Default: `optix_user`
- `DB_PASSWORD_OPTIX2` - Strong password (20+ characters)

### CRM Database

- `DB_DATABASE_CRM` - Default: `optical_crm`
- `DB_USERNAME_CRM` - Default: `crm_user`
- `DB_PASSWORD_CRM` - Strong password (20+ characters)

### Application Configuration

- `APP_NAME` - Default: `OpticalCRM`
- `APP_ENV` - Default: `production`
- `APP_KEY` - Auto-generated if empty
- `APP_DEBUG` - Default: `false`
- `APP_URL` - Your application URL

## Generate Strong Passwords

```bash
# Generate random password (32 characters)
openssl rand -base64 32

# Or use:
openssl rand -hex 20
```

## Important Notes

1. **DO NOT commit `.env` file to version control**
2. **All passwords must be changed before production**
3. **APP_KEY will be automatically generated on first start**
4. **Minimum password length: 20 characters recommended**
