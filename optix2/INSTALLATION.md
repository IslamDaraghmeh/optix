# Quick Installation Guide

## Prerequisites Checklist

- [ ] PHP 8.1+ installed
- [ ] MySQL 8.0+ or MariaDB 10.5+ installed
- [ ] Composer installed
- [ ] Web server (Apache/Nginx) configured
- [ ] Required PHP extensions enabled

## Installation Steps

### 1. Install Dependencies

```bash
cd /path/to/optix
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
nano .env  # Edit with your settings
```

**Key settings to update:**
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `MAIL_*` settings for email functionality
- `APP_URL` to match your domain

### 3. Set Permissions

```bash
chmod -R 755 storage/
chown -R www-data:www-data storage/  # Linux/Ubuntu
```

For Windows (XAMPP/WAMP), ensure the web server has write permissions to the `storage/` directory.

### 4. Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE optix_clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 5. Import Database Schema

```bash
mysql -u root -p optix_clinic < database/schema.sql
mysql -u root -p optix_clinic < database/seeds/001_locations.sql
mysql -u root -p optix_clinic < database/seeds/002_users.sql
mysql -u root -p optix_clinic < database/seeds/003_sample_data.sql
mysql -u root -p optix_clinic < database/migrations/add_password_resets.sql
```

### 6. Configure Web Server

#### Apache

Ensure `.htaccess` is working and `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx

Use the configuration provided in README.md.

### 7. Test Installation

Open your browser and navigate to:
```
http://localhost/optix/public
```

or your configured domain.

### 8. Login

Use the default admin credentials:
- **Email**: admin@optixclinic.com
- **Password**: password123

**IMPORTANT**: Change this password immediately!

## Verification

After installation, verify:

1. [ ] Login page loads without errors
2. [ ] Can log in with default credentials
3. [ ] Dashboard displays correctly
4. [ ] Database tables are created (22+ tables)
5. [ ] Sample data is present
6. [ ] File uploads work (test patient photo upload)
7. [ ] No permission errors in storage directories

## Troubleshooting

### Can't connect to database
- Check `.env` database credentials
- Ensure MySQL is running: `sudo systemctl status mysql`
- Verify database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### 404 errors on all pages
- Check Apache `.htaccess` is being read
- Ensure `mod_rewrite` is enabled
- Verify DocumentRoot points to `public/` directory

### Permission errors
```bash
chmod -R 755 storage/
chown -R www-data:www-data storage/
```

### Composer errors
```bash
composer update
composer dump-autoload
```

## Post-Installation

1. **Change default passwords** for all test accounts
2. **Configure email settings** in `.env`
3. **Set up backup schedule** for database
4. **Review security settings** in README.md
5. **Configure SSL/HTTPS** for production

## Need Help?

Refer to the comprehensive README.md for detailed documentation.

---

**Installation Time**: ~15-30 minutes
**Difficulty**: Intermediate
