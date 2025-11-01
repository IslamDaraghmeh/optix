# Optix Clinic Management System - PHP Backend API

A comprehensive, enterprise-grade clinic management system built with PHP 8.1+, MySQL, and modern best practices.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [Project Structure](#project-structure)
- [Core Components](#core-components)
- [Security Features](#security-features)
- [Usage Examples](#usage-examples)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [License](#license)

## Features

### Clinical Management
- **Patient Management**: Complete patient records with demographics, medical history, and contact information
- **Eye Examinations**: Comprehensive exam forms with refraction, visual acuity, IOP, anterior/posterior segment findings
- **Prescriptions**: Eyeglasses and contact lens prescriptions with automatic expiration tracking
- **Appointments**: Full scheduling system with availability checking, reminders, and calendar views

### Business Operations
- **Point of Sale (POS)**: Complete sales transactions with tax calculation and multiple payment methods
- **Inventory Management**: Product catalog, stock levels, low stock alerts, and automatic adjustments
- **Insurance Management**: Insurance provider management, patient insurance, and claims tracking
- **Lab Orders**: Eyeglass and contact lens lab order management with tracking

### Reporting & Analytics
- **Financial Reports**: Daily sales, revenue analysis, payment method breakdown
- **Clinical Reports**: Patient growth, examination statistics, prescription trends
- **Inventory Reports**: Stock levels, product performance, reorder alerts
- **Insurance Reports**: Claims status, reimbursement tracking

### System Features
- **Role-Based Access Control (RBAC)**: Admin, Doctor, Optometrist, Optician, Receptionist, Manager, Cashier
- **Audit Logging**: Complete activity tracking for compliance
- **Email Notifications**: Appointment reminders, receipts, password resets
- **PDF Generation**: Receipts, prescriptions, examination reports
- **File Uploads**: Patient photos, retinal images, OCT scans

## Requirements

### Server Requirements
- **PHP**: 8.1 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Composer**: 2.0+

### PHP Extensions
- `pdo_mysql` - Database connectivity
- `mbstring` - Multibyte string handling
- `gd` - Image processing
- `curl` - HTTP requests
- `zip` - Archive handling
- `openssl` - Encryption

### Optional
- **Node.js & npm**: For frontend asset compilation (if adding frontend)
- **Redis**: For session storage and caching (production recommended)

## Installation

### Step 1: Clone or Extract Project

```bash
# If using Git
git clone <repository-url> optix
cd optix

# Or extract the zip file to your web server directory
```

### Step 2: Install Dependencies

```bash
composer install
```

This will install:
- PHPMailer (email sending)
- DomPDF (PDF generation)
- phpdotenv (environment configuration)
- ramsey/uuid (unique identifiers)

### Step 3: Configure Environment

```bash
# Copy the example environment file
cp .env.example .env

# Edit .env with your settings
nano .env  # or use your preferred editor
```

### Step 4: Set Permissions

```bash
# Make storage directories writable
chmod -R 755 storage/
chmod -R 755 storage/logs/
chmod -R 755 storage/uploads/
chmod -R 755 storage/cache/

# Ensure web server can write to these directories
chown -R www-data:www-data storage/  # For Apache/Ubuntu
# OR
chown -R apache:apache storage/       # For Apache/CentOS
```

## Configuration

### Environment Variables (.env)

Edit the `.env` file with your specific settings:

```env
# Application
APP_NAME="Optix Clinic Management System"
APP_ENV=development  # development, production
APP_DEBUG=true       # Set to false in production
APP_URL=http://localhost/optix/public
APP_TIMEZONE=America/New_York

# Database
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=optix_clinic
DB_USERNAME=root
DB_PASSWORD=your_password

# Email (SMTP)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@optixclinic.com

# Security
SESSION_LIFETIME=7200  # 2 hours
PASSWORD_MIN_LENGTH=8

# Business Settings
TAX_RATE=0.08  # 8%
LOW_STOCK_THRESHOLD=10
APPOINTMENT_SLOT_DURATION=30  # minutes
```

### Web Server Configuration

#### Apache Configuration

The project includes `.htaccess` files. Ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Your virtual host should point to the `public/` directory:

```apache
<VirtualHost *:80>
    ServerName optix.local
    DocumentRoot /var/www/optix/public

    <Directory /var/www/optix/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/optix_error.log
    CustomLog ${APACHE_LOG_DIR}/optix_access.log combined
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name optix.local;
    root /var/www/optix/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## Database Setup

### Step 1: Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE optix_clinic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'optix_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON optix_clinic.* TO 'optix_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Step 2: Import Schema

```bash
mysql -u root -p optix_clinic < database/schema.sql
```

### Step 3: Import Seed Data

```bash
mysql -u root -p optix_clinic < database/seeds/001_locations.sql
mysql -u root -p optix_clinic < database/seeds/002_users.sql
mysql -u root -p optix_clinic < database/seeds/003_sample_data.sql
mysql -u root -p optix_clinic < database/migrations/add_password_resets.sql
```

### Default Login Credentials

After importing seed data, you can log in with:

- **Admin Account**:
  - Email: `admin@optixclinic.com`
  - Password: `password123`

- **Other Test Accounts**:
  - Doctor: `doctor@optixclinic.com`
  - Optometrist: `optometrist@optixclinic.com`
  - Receptionist: `receptionist@optixclinic.com`
  - All use password: `password123`

**IMPORTANT**: Change these passwords immediately in production!

## Project Structure

```
optix/
├── app/
│   ├── Controllers/          # Application controllers
│   │   ├── BaseController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── PatientController.php
│   │   └── ...
│   ├── Models/               # Data models
│   │   ├── BaseModel.php
│   │   ├── Patient.php
│   │   ├── Appointment.php
│   │   ├── Transaction.php
│   │   └── ...
│   ├── Helpers/              # Helper classes
│   │   ├── Database.php      # PDO wrapper
│   │   ├── Auth.php          # Authentication
│   │   ├── Security.php      # CSRF, XSS protection
│   │   ├── Validator.php     # Input validation
│   │   ├── Session.php       # Session management
│   │   ├── Email.php         # Email sending
│   │   ├── PDF.php           # PDF generation
│   │   └── FileUpload.php    # File handling
│   └── Views/                # View templates (to be created)
├── config/
│   ├── config.php            # Main configuration
│   ├── database.php          # Database config
│   └── constants.php         # Application constants
├── database/
│   ├── schema.sql            # Database schema
│   ├── seeds/                # Seed data files
│   └── migrations/           # Database migrations
├── public/                   # Web root
│   ├── index.php             # Front controller
│   ├── .htaccess             # Apache rewrite rules
│   ├── css/                  # Stylesheets
│   ├── js/                   # JavaScript files
│   └── images/               # Image assets
├── storage/
│   ├── logs/                 # Application logs
│   ├── uploads/              # Uploaded files
│   └── cache/                # Cache files
├── vendor/                   # Composer dependencies
├── .env.example              # Environment template
├── .gitignore                # Git ignore rules
├── composer.json             # PHP dependencies
└── README.md                 # This file
```

## Core Components

### Helper Classes

#### Database Helper
```php
use App\Helpers\Database;

$db = Database::getInstance();

// Select operations
$users = $db->select("SELECT * FROM users WHERE role = ?", ['admin']);
$user = $db->selectOne("SELECT * FROM users WHERE id = ?", [1]);

// Insert
$userId = $db->insert('users', [
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email' => 'john@example.com',
    'password' => password_hash('password', PASSWORD_BCRYPT)
]);

// Update
$db->update('users', ['status' => 'active'], 'id = ?', [1]);

// Delete
$db->delete('users', 'id = ?', [1]);

// Transactions
$db->transaction(function($db) {
    $db->insert('table1', $data1);
    $db->insert('table2', $data2);
});
```

#### Authentication
```php
use App\Helpers\Auth;

$auth = new Auth();

// Login
if ($auth->login($email, $password)) {
    // Success
}

// Check authentication
if ($auth->check()) {
    $user = $auth->user();
}

// Check permissions
if ($auth->hasPermission(PERM_MANAGE_PATIENTS)) {
    // User has permission
}

// Logout
$auth->logout();
```

#### Validation
```php
use App\Helpers\Validator;

$validator = new Validator();
$data = $_POST;

$rules = [
    'email' => 'required|email|unique:users,email',
    'password' => 'required|min:8',
    'name' => 'required|alpha|min:2|max:100',
    'age' => 'numeric|min:0|max:150',
];

if ($validator->validate($data, $rules)) {
    // Valid
} else {
    $errors = $validator->getErrors();
}
```

### Base Classes

#### BaseController
All controllers extend `BaseController` which provides:
- Authentication checks
- Permission verification
- CSRF protection
- JSON responses
- View rendering
- Flash messages

```php
class MyController extends BaseController
{
    public function index()
    {
        $this->requireAuth();
        $this->requirePermission(PERM_VIEW_PATIENTS);

        $data = $this->patientModel->all();
        $this->view('patients/index', ['patients' => $data]);
    }
}
```

#### BaseModel
All models extend `BaseModel` which provides:
- CRUD operations
- Soft deletes
- Timestamps
- Pagination
- Query builders

```php
class Patient extends BaseModel
{
    protected string $table = 'patients';

    public function findActive()
    {
        return $this->where("status = 'active'");
    }
}
```

## Security Features

### Implemented Security Measures

1. **Password Hashing**: BCrypt with cost factor 12
2. **CSRF Protection**: Token-based protection on all forms
3. **XSS Prevention**: Input sanitization and output escaping
4. **SQL Injection Prevention**: PDO prepared statements
5. **Session Security**: Secure, HTTPOnly, SameSite cookies
6. **Account Lockout**: 5 failed attempts = 30-minute lockout
7. **File Upload Validation**: Type, size, and MIME type checking
8. **Audit Logging**: All sensitive operations logged
9. **Role-Based Access Control**: Granular permissions
10. **Security Headers**: X-Frame-Options, X-XSS-Protection, etc.

### Security Best Practices

- Always use HTTPS in production
- Set `APP_DEBUG=false` in production
- Use strong database passwords
- Regularly update dependencies: `composer update`
- Enable PHP opcache in production
- Implement rate limiting on login endpoints
- Regular security audits
- Keep PHP and MySQL updated

## Usage Examples

### Creating a Patient

```php
use App\Models\Patient;

$patient = new Patient();
$data = [
    'patient_number' => $patient->generatePatientNumber(),
    'first_name' => 'John',
    'last_name' => 'Doe',
    'date_of_birth' => '1990-01-15',
    'gender' => 'male',
    'email' => 'john.doe@email.com',
    'phone' => '(555) 123-4567',
];

$patientId = $patient->create($data);
```

### Creating an Appointment

```php
use App\Models\Appointment;

$appointment = new Appointment();

// Check availability
if ($appointment->checkAvailability('2025-10-15', '10:00:00', $providerId, 30)) {
    $appointmentId = $appointment->create([
        'patient_id' => $patientId,
        'provider_id' => $providerId,
        'location_id' => $locationId,
        'appointment_date' => '2025-10-15',
        'appointment_time' => '10:00:00',
        'duration' => 30,
        'appointment_type' => 'comprehensive',
        'status' => 'scheduled',
    ]);
}
```

### Processing a Sale

```php
use App\Models\Transaction;

$transaction = new Transaction();

$transactionData = [
    'patient_id' => $patientId,
    'location_id' => $locationId,
    'cashier_id' => $userId,
    'transaction_date' => date('Y-m-d'),
    'transaction_time' => date('H:i:s'),
    'subtotal' => 100.00,
    'tax' => 8.00,
    'total' => 108.00,
    'amount_paid' => 108.00,
    'status' => 'completed',
];

$items = [
    [
        'product_id' => 1,
        'product_name' => 'Frame',
        'quantity' => 1,
        'unit_price' => 100.00,
        'line_total' => 100.00,
    ]
];

$transactionId = $transaction->createWithItems($transactionData, $items);
```

### Generating a PDF Receipt

```php
use App\Helpers\PDF;

$pdf = new PDF();
$transaction = $transactionModel->getWithItems($transactionId);
$items = $transaction['items'];

$pdfContent = $pdf->generateReceipt($transaction, $items);

// Save to file
file_put_contents('receipt.pdf', $pdfContent);

// Or download directly
$pdf->download($html, 'receipt_' . $transaction['id'] . '.pdf');
```

## API Documentation

### Routing

URLs follow the pattern: `/controller/method/param1/param2`

Examples:
- `/patient` → PatientController::index()
- `/patient/view/123` → PatientController::view(123)
- `/patient/edit/123` → PatientController::edit(123)
- `/appointment/calendar` → AppointmentController::calendar()

### Common Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/` | Dashboard |
| GET/POST | `/login` | Login page/action |
| GET | `/logout` | Logout |
| GET | `/patient` | List patients |
| GET | `/patient/view/{id}` | View patient |
| GET/POST | `/patient/create` | Create patient |
| GET/POST | `/patient/edit/{id}` | Edit patient |
| POST | `/patient/delete/{id}` | Delete patient |

## Testing

### Manual Testing

1. Access the application: `http://localhost/optix/public`
2. Login with default credentials
3. Test each module:
   - Create a patient
   - Schedule an appointment
   - Record an examination
   - Process a sale
   - Generate reports

### Database Testing

```bash
# Test database connection
mysql -u optix_user -p optix_clinic -e "SELECT COUNT(*) FROM users;"

# Check data integrity
mysql -u optix_user -p optix_clinic -e "SELECT * FROM users LIMIT 5;"
```

## Deployment

### Production Checklist

1. **Environment**
   - [ ] Set `APP_ENV=production`
   - [ ] Set `APP_DEBUG=false`
   - [ ] Use strong `DB_PASSWORD`
   - [ ] Configure proper `APP_URL`

2. **Security**
   - [ ] Enable HTTPS
   - [ ] Change all default passwords
   - [ ] Restrict database access
   - [ ] Set proper file permissions (644 files, 755 directories)
   - [ ] Remove `.env.example` from production

3. **Performance**
   - [ ] Enable PHP OPcache
   - [ ] Configure MySQL query cache
   - [ ] Set up Redis for sessions (optional)
   - [ ] Enable GZIP compression

4. **Monitoring**
   - [ ] Set up error logging
   - [ ] Configure log rotation
   - [ ] Set up backups
   - [ ] Monitor disk space

5. **Backup Strategy**
```bash
# Database backup script
#!/bin/bash
mysqldump -u optix_user -p optix_clinic > backup_$(date +%Y%m%d_%H%M%S).sql
```

## Troubleshooting

### Common Issues

**Issue**: Can't connect to database
```bash
# Check MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u optix_user -p optix_clinic
```

**Issue**: Permission denied on storage directories
```bash
chmod -R 755 storage/
chown -R www-data:www-data storage/
```

**Issue**: 404 errors on all pages
```bash
# Enable mod_rewrite (Apache)
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check .htaccess is being read
```

**Issue**: Composer dependencies not found
```bash
composer dump-autoload
```

## Database Schema Overview

### Core Tables
- **users**: Staff members and authentication
- **locations**: Clinic locations
- **patients**: Patient demographics and contact info
- **examinations**: Eye exam records
- **prescriptions**: Eyeglass/contact lens prescriptions
- **appointments**: Appointment scheduling

### Business Tables
- **products**: Inventory items catalog
- **inventory**: Stock levels by location
- **transactions**: Sales transactions
- **transaction_items**: Line items for sales
- **payments**: Payment records

### Insurance Tables
- **insurance_providers**: Insurance companies
- **patient_insurance**: Patient insurance policies
- **insurance_claims**: Claims tracking

### Supporting Tables
- **suppliers**: Product suppliers
- **lab_orders**: Eyeglass lab orders
- **settings**: System configuration
- **audit_logs**: Activity tracking
- **failed_login_attempts**: Security tracking

## Contributing

### Development Guidelines

1. Follow PSR-12 coding standards
2. Add PHPDoc comments to all classes and methods
3. Write meaningful commit messages
4. Test thoroughly before committing
5. Keep functions under 50 lines when possible
6. Use type hinting for parameters and return types

### Code Style

```php
<?php
/**
 * Class description
 *
 * @package App\Models
 * @author Your Name
 * @version 1.0
 */
class MyClass
{
    /**
     * Method description
     *
     * @param string $param Parameter description
     * @return bool Return description
     */
    public function myMethod(string $param): bool
    {
        // Implementation
        return true;
    }
}
```

## License

Proprietary - All rights reserved

## Support

For technical support, please contact:
- Technical Lead: [email@domain.com]
- Documentation: [URL]
- Issue Tracker: [URL]

## Credits

Developed by the Optix Development Team

### Technologies Used
- PHP 8.1+
- MySQL 8.0+
- PHPMailer 6.8
- DomPDF 2.0
- phpdotenv 5.5
- Ramsey UUID 4.7

---

**Version**: 1.0
**Last Updated**: October 3, 2025
**Maintained By**: Optix Development Team
