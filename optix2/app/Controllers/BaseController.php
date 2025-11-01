<?php
/**
 * Base Controller Class
 *
 * Parent class for all controllers providing common functionality
 *
 * @package App\Controllers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Controllers;

use App\Helpers\Database;
use App\Helpers\Auth;
use App\Helpers\Security;
use App\Helpers\Session;
use App\Helpers\Validator;

abstract class BaseController
{
    /**
     * @var Database Database instance
     */
    protected Database $db;

    /**
     * @var Auth Auth instance
     */
    protected Auth $auth;

    /**
     * @var Security Security instance
     */
    protected Security $security;

    /**
     * @var Session Session instance
     */
    protected Session $session;

    /**
     * @var Validator Validator instance
     */
    protected Validator $validator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->session = Session::getInstance();
        $this->security = new Security();
        $this->auth = new Auth();
        $this->validator = new Validator();

        // Set security headers
        $this->security->setSecurityHeaders();
    }

    /**
     * Load and render view
     *
     * @param string $view View file name (without .php)
     * @param array $data Data to pass to view
     * @return void
     */
    protected function view(string $view, array $data = []): void
    {
        // Extract data to variables
        extract($data);

        // Get flash messages
        $flash = $this->session->getAllFlash();

        // Get current user
        $currentUser = $this->auth->user();

        // CSRF token
        $csrfToken = $this->security->generateCsrfToken();
        $csrfField = $this->security->getCsrfField();

        // View path
        $viewPath = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            $this->error404("View not found: {$view}");
        }
    }

    /**
     * Return JSON response
     *
     * @param mixed $data Data to return
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to URL
     *
     * @param string $url URL to redirect to
     * @return void
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Redirect back to previous page
     *
     * @return void
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? APP_URL;
        $this->redirect($referer);
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        return $this->auth->check();
    }

    /**
     * Require authentication
     *
     * @return void
     */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated()) {
            $this->session->setFlash('error', 'Please log in to access this page');
            $this->redirect(APP_URL . '/login');
        }
    }

    /**
     * Check if user has permission
     *
     * @param string $permission Permission name
     * @return bool
     */
    protected function hasPermission(string $permission): bool
    {
        return $this->auth->hasPermission($permission);
    }

    /**
     * Require specific permission
     *
     * @param string $permission Permission name
     * @return void
     */
    protected function requirePermission(string $permission): void
    {
        $this->requireAuth();

        if (!$this->hasPermission($permission)) {
            $this->error403();
        }
    }

    /**
     * Check if user has role
     *
     * @param string $role Role name
     * @return bool
     */
    protected function hasRole(string $role): bool
    {
        return $this->auth->hasRole($role);
    }

    /**
     * Require specific role
     *
     * @param string $role Role name
     * @return void
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();

        if (!$this->hasRole($role)) {
            $this->error403();
        }
    }

    /**
     * Get current user
     *
     * @return array|null
     */
    protected function getCurrentUser(): ?array
    {
        return $this->auth->user();
    }

    /**
     * Verify CSRF token
     *
     * @return bool
     */
    protected function verifyCsrfToken(): bool
    {
        $token = $_POST[CSRF_TOKEN_NAME] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        return $this->security->verifyCsrfToken($token);
    }

    /**
     * Require CSRF token validation
     *
     * @return void
     */
    protected function requireCsrfToken(): void
    {
        if (!$this->verifyCsrfToken()) {
            $this->error403('Invalid CSRF token');
        }
    }

    /**
     * Check if request is POST
     *
     * @return bool
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is GET
     *
     * @return bool
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Check if request is AJAX
     *
     * @return bool
     */
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Get POST data
     *
     * @param string|null $key Key to get (null for all)
     * @param mixed $default Default value
     * @return mixed
     */
    protected function post(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     *
     * @param string|null $key Key to get (null for all)
     * @param mixed $default Default value
     * @return mixed
     */
    protected function get(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Validate request data
     *
     * @param array $data Data to validate
     * @param array $rules Validation rules
     * @return bool
     */
    protected function validate(array $data, array $rules): bool
    {
        return $this->validator->validate($data, $rules);
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    protected function getValidationErrors(): array
    {
        return $this->validator->getErrors();
    }

    /**
     * Set flash message and redirect
     *
     * @param string $type Message type
     * @param string $message Message content
     * @param string $url Redirect URL
     * @return void
     */
    protected function flashAndRedirect(string $type, string $message, string $url): void
    {
        $this->session->setFlash($type, $message);
        $this->redirect($url);
    }

    /**
     * Handle 404 error
     *
     * @param string $message Error message
     * @return void
     */
    protected function error404(string $message = 'Page not found'): void
    {
        http_response_code(404);

        if ($this->isAjax()) {
            $this->json(['error' => $message], 404);
        } else {
            $this->view('errors/404', ['message' => $message]);
        }
        exit;
    }

    /**
     * Handle 403 error
     *
     * @param string $message Error message
     * @return void
     */
    protected function error403(string $message = 'Access denied'): void
    {
        http_response_code(403);

        if ($this->isAjax()) {
            $this->json(['error' => $message], 403);
        } else {
            $this->view('errors/403', ['message' => $message]);
        }
        exit;
    }

    /**
     * Handle 500 error
     *
     * @param string $message Error message
     * @return void
     */
    protected function error500(string $message = 'Internal server error'): void
    {
        http_response_code(500);

        if ($this->isAjax()) {
            $this->json(['error' => $message], 500);
        } else {
            $this->view('errors/500', ['message' => $message]);
        }
        exit;
    }

    /**
     * Log activity
     *
     * @param string $action Action performed
     * @param string $description Description
     * @return void
     */
    protected function logActivity(string $action, string $description): void
    {
        $userId = $this->auth->getUserId();

        if ($userId) {
            $this->db->insert('audit_logs', [
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'created_at' => date(DATETIME_FORMAT),
            ]);
        }
    }
}
