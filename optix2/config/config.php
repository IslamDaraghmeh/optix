<?php
/**
 * Main Application Configuration File
 *
 * This file loads environment variables and defines application-wide constants
 *
 * @package Optix
 * @author Optix Development Team
 * @version 1.0
 */

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('UPLOAD_PATH', STORAGE_PATH . '/uploads');
define('LOG_PATH', STORAGE_PATH . '/logs');
define('CACHE_PATH', STORAGE_PATH . '/cache');

// Application configuration
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Optix Clinic Management System');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?? 'UTC');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Database configuration
define('DB_CONNECTION', $_ENV['DB_CONNECTION'] ?? 'mysql');
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? 'optix_clinic');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');
define('DB_COLLATION', $_ENV['DB_COLLATION'] ?? 'utf8mb4_unicode_ci');

// Security configuration
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 7200));
define('SESSION_SECURE', filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('SESSION_HTTPONLY', filter_var($_ENV['SESSION_HTTPONLY'] ?? true, FILTER_VALIDATE_BOOLEAN));
define('SESSION_SAMESITE', $_ENV['SESSION_SAMESITE'] ?? 'Strict');
define('CSRF_TOKEN_NAME', $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token');
define('PASSWORD_MIN_LENGTH', (int)($_ENV['PASSWORD_MIN_LENGTH'] ?? 8));

// Email configuration
define('MAIL_MAILER', $_ENV['MAIL_MAILER'] ?? 'smtp');
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com');
define('MAIL_PORT', (int)($_ENV['MAIL_PORT'] ?? 587));
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? 'tls');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@optixclinic.com');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? APP_NAME);

// File upload configuration
define('UPLOAD_MAX_SIZE', (int)($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880)); // 5MB default
define('UPLOAD_ALLOWED_TYPES', $_ENV['UPLOAD_ALLOWED_TYPES'] ?? 'jpg,jpeg,png,pdf,doc,docx');

// PDF configuration
define('PDF_FONT_SIZE', (int)($_ENV['PDF_FONT_SIZE'] ?? 10));
define('PDF_PAPER_SIZE', $_ENV['PDF_PAPER_SIZE'] ?? 'letter');
define('PDF_ORIENTATION', $_ENV['PDF_ORIENTATION'] ?? 'portrait');

// Pagination
define('PAGINATION_PER_PAGE', (int)($_ENV['PAGINATION_PER_PAGE'] ?? 20));

// Business logic configuration
define('APPOINTMENT_SLOT_DURATION', (int)($_ENV['APPOINTMENT_SLOT_DURATION'] ?? 30));
define('APPOINTMENT_REMINDER_HOURS', (int)($_ENV['APPOINTMENT_REMINDER_HOURS'] ?? 24));
define('LOW_STOCK_THRESHOLD', (int)($_ENV['LOW_STOCK_THRESHOLD'] ?? 10));
define('TAX_RATE', (float)($_ENV['TAX_RATE'] ?? 0.08));

// Logging
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'debug');

// Error reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', LOG_PATH . '/php_errors.log');
}

return [
    'app' => [
        'name' => APP_NAME,
        'env' => APP_ENV,
        'debug' => APP_DEBUG,
        'url' => APP_URL,
        'timezone' => APP_TIMEZONE,
    ],
    'database' => [
        'connection' => DB_CONNECTION,
        'host' => DB_HOST,
        'port' => DB_PORT,
        'database' => DB_DATABASE,
        'username' => DB_USERNAME,
        'password' => DB_PASSWORD,
        'charset' => DB_CHARSET,
        'collation' => DB_COLLATION,
    ],
    'session' => [
        'lifetime' => SESSION_LIFETIME,
        'secure' => SESSION_SECURE,
        'httponly' => SESSION_HTTPONLY,
        'samesite' => SESSION_SAMESITE,
    ],
    'mail' => [
        'mailer' => MAIL_MAILER,
        'host' => MAIL_HOST,
        'port' => MAIL_PORT,
        'username' => MAIL_USERNAME,
        'password' => MAIL_PASSWORD,
        'encryption' => MAIL_ENCRYPTION,
        'from' => [
            'address' => MAIL_FROM_ADDRESS,
            'name' => MAIL_FROM_NAME,
        ],
    ],
];
