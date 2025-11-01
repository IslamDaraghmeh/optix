<?php
/**
 * Security Helper Class
 *
 * Handles CSRF protection, input sanitization, and XSS prevention
 *
 * @package App\Helpers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Helpers;

use App\Helpers\Session;

class Security
{
    /**
     * @var Session Session instance
     */
    private Session $session;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->session = Session::getInstance();
    }

    /**
     * Generate CSRF token
     *
     * @return string
     */
    public function generateCsrfToken(): string
    {
        if (!$this->session->has(CSRF_TOKEN_NAME)) {
            $token = bin2hex(random_bytes(32));
            $this->session->set(CSRF_TOKEN_NAME, $token);
        }

        return $this->session->get(CSRF_TOKEN_NAME);
    }

    /**
     * Verify CSRF token
     *
     * @param string|null $token Token to verify
     * @return bool
     */
    public function verifyCsrfToken(?string $token): bool
    {
        $sessionToken = $this->session->get(CSRF_TOKEN_NAME);

        if (!$sessionToken || !$token) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Get CSRF token input field HTML
     *
     * @return string
     */
    public function getCsrfField(): string
    {
        $token = $this->generateCsrfToken();
        return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
    }

    /**
     * Sanitize string input
     *
     * @param string $input Input string
     * @return string
     */
    public function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize email
     *
     * @param string $email Email address
     * @return string|false
     */
    public function sanitizeEmail(string $email)
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize URL
     *
     * @param string $url URL
     * @return string|false
     */
    public function sanitizeUrl(string $url)
    {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }

    /**
     * Sanitize integer
     *
     * @param mixed $value Value to sanitize
     * @return int
     */
    public function sanitizeInt($value): int
    {
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitize float
     *
     * @param mixed $value Value to sanitize
     * @return float
     */
    public function sanitizeFloat($value): float
    {
        return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitize array of strings
     *
     * @param array $array Input array
     * @return array
     */
    public function sanitizeArray(array $array): array
    {
        return array_map([$this, 'sanitizeString'], $array);
    }

    /**
     * Escape HTML output
     *
     * @param string $string String to escape
     * @return string
     */
    public function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Remove XSS threats from input
     *
     * @param string $data Input data
     * @return string
     */
    public function xssClean(string $data): string
    {
        // Fix &entity\n;
        $data = str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        return $data;
    }

    /**
     * Validate file upload
     *
     * @param array $file $_FILES array element
     * @param array $allowedTypes Allowed file extensions
     * @param int $maxSize Maximum file size in bytes
     * @return array ['success' => bool, 'message' => string]
     */
    public function validateFileUpload(array $file, array $allowedTypes = [], int $maxSize = 0): array
    {
        // Check if file was uploaded
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'message' => 'Invalid file upload'];
        }

        // Check for upload errors
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'message' => 'No file was uploaded'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'message' => 'File exceeds maximum size'];
            default:
                return ['success' => false, 'message' => 'Unknown upload error'];
        }

        // Validate file size
        $maxSize = $maxSize ?: UPLOAD_MAX_SIZE;
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File size exceeds maximum allowed size'];
        }

        // Validate file type
        $allowedTypes = $allowedTypes ?: explode(',', UPLOAD_ALLOWED_TYPES);
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedTypes)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (isset($allowedMimes[$extension]) && $mimeType !== $allowedMimes[$extension]) {
            return ['success' => false, 'message' => 'Invalid file type'];
        }

        return ['success' => true, 'message' => 'File validation successful'];
    }

    /**
     * Generate secure random string
     *
     * @param int $length String length
     * @return string
     */
    public function generateRandomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Hash data
     *
     * @param string $data Data to hash
     * @return string
     */
    public function hash(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * Encrypt data
     *
     * @param string $data Data to encrypt
     * @param string $key Encryption key
     * @return string
     */
    public function encrypt(string $data, string $key): string
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Decrypt data
     *
     * @param string $data Encrypted data
     * @param string $key Encryption key
     * @return string|false
     */
    public function decrypt(string $data, string $key)
    {
        $parts = explode('::', base64_decode($data), 2);
        if (count($parts) !== 2) {
            return false;
        }

        [$encrypted, $iv] = $parts;
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Prevent clickjacking by setting X-Frame-Options header
     *
     * @return void
     */
    public function preventClickjacking(): void
    {
        header('X-Frame-Options: SAMEORIGIN');
    }

    /**
     * Set security headers
     *
     * @return void
     */
    public function setSecurityHeaders(): void
    {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');

        if (SESSION_SECURE) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}
