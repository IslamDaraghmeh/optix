<?php
/**
 * Session Helper Class
 *
 * Handles session management, flash messages, and old input storage
 *
 * @package App\Helpers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Helpers;

class Session
{
    /**
     * @var Session|null Singleton instance
     */
    private static ?Session $instance = null;

    /**
     * @var bool Flag to track if session has been started
     */
    private static bool $started = false;

    /**
     * Constructor - Start session if not already started
     */
    public function __construct()
    {
        $this->start();
    }

    /**
     * Get singleton instance
     *
     * @return Session
     */
    public static function getInstance(): Session
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Start session with security settings
     *
     * @return void
     */
    public function start(): void
    {
        if (self::$started) {
            return;
        }

        if (session_status() === PHP_SESSION_NONE) {
            // Set session cookie parameters
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'domain' => '',
                'secure' => SESSION_SECURE,
                'httponly' => SESSION_HTTPONLY,
                'samesite' => SESSION_SAMESITE,
            ]);

            // Set session name
            session_name('OPTIX_SESSION');

            // Start the session
            session_start();

            self::$started = true;

            // Regenerate session ID periodically for security
            if (!isset($_SESSION['last_regeneration'])) {
                $this->regenerate();
            } elseif (time() - ($_SESSION['last_regeneration'] ?? 0) > 300) {
                $this->regenerate();
            }
        }
    }

    /**
     * Regenerate session ID
     *
     * @param bool $deleteOldSession Delete old session data
     * @return void
     */
    public function regenerate(bool $deleteOldSession = false): void
    {
        session_regenerate_id($deleteOldSession);
        $this->set('last_regeneration', time());
    }

    /**
     * Set session variable
     *
     * @param string $key Session key
     * @param mixed $value Session value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get session variable
     *
     * @param string $key Session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session variable exists
     *
     * @param string $key Session key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session variable
     *
     * @param string $key Session key
     * @return void
     */
    public function remove(string $key): void
    {
        if ($this->has($key)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Set flash message
     *
     * @param string $type Message type (success, error, warning, info)
     * @param string $message Message content
     * @return void
     */
    public function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get flash message and remove it
     *
     * @param string $type Message type
     * @return string|null
     */
    public function getFlash(string $type): ?string
    {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }

    /**
     * Check if flash message exists
     *
     * @param string $type Message type
     * @return bool
     */
    public function hasFlash(string $type): bool
    {
        return isset($_SESSION['flash'][$type]);
    }

    /**
     * Get all flash messages
     *
     * @return array
     */
    public function getAllFlash(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $messages;
    }

    /**
     * Set old input for form repopulation
     *
     * @param array $input Input data
     * @return void
     */
    public function setOldInput(array $input): void
    {
        $_SESSION['old_input'] = $input;
    }

    /**
     * Get old input value
     *
     * @param string $key Input key
     * @param mixed $default Default value
     * @return mixed
     */
    public function getOldInput(string $key, $default = '')
    {
        return $_SESSION['old_input'][$key] ?? $default;
    }

    /**
     * Check if old input exists
     *
     * @param string $key Input key
     * @return bool
     */
    public function hasOldInput(string $key): bool
    {
        return isset($_SESSION['old_input'][$key]);
    }

    /**
     * Clear old input
     *
     * @return void
     */
    public function clearOldInput(): void
    {
        unset($_SESSION['old_input']);
    }

    /**
     * Flash input for next request
     *
     * @return void
     */
    public function flashInput(): void
    {
        $this->setOldInput($_POST);
    }

    /**
     * Get all session data
     *
     * @return array
     */
    public function all(): array
    {
        return $_SESSION;
    }

    /**
     * Clear all session data
     *
     * @return void
     */
    public function clear(): void
    {
        $_SESSION = [];
    }

    /**
     * Destroy session
     *
     * @return void
     */
    public function destroy(): void
    {
        $_SESSION = [];

        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Get session ID
     *
     * @return string
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Set session ID
     *
     * @param string $id Session ID
     * @return void
     */
    public function setId(string $id): void
    {
        session_id($id);
    }
}
