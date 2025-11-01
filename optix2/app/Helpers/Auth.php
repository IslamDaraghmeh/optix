<?php
/**
 * Authentication Helper Class
 *
 * Handles user authentication, authorization, and role-based access control
 *
 * @package App\Helpers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Helpers;

use App\Helpers\Database;
use App\Helpers\Session;

class Auth
{
    /**
     * @var Database Database instance
     */
    private Database $db;

    /**
     * @var Session Session instance
     */
    private Session $session;

    /**
     * Maximum failed login attempts
     */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Account lockout duration in seconds (30 minutes)
     */
    private const LOCKOUT_DURATION = 1800;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->session = Session::getInstance();
    }

    /**
     * Attempt to log in a user
     *
     * @param string $email User email
     * @param string $password User password
     * @return bool True on success, false on failure
     */
    public function login(string $email, string $password): bool
    {
        // Check if account is locked
        if ($this->isAccountLocked($email)) {
            $this->session->setFlash('error', 'Account is locked due to too many failed login attempts. Please try again later.');
            return false;
        }

        // Get user by email
        $user = $this->db->selectOne(
            "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL",
            [$email]
        );

        if (!$user) {
            $this->recordFailedLogin($email);
            return false;
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            $this->recordFailedLogin($email);
            return false;
        }

        // Check if user is active
        if ($user['status'] !== 'active') {
            $this->session->setFlash('error', 'Your account is not active. Please contact an administrator.');
            return false;
        }

        // Clear failed login attempts
        $this->clearFailedLogins($email);

        // Set session data
        $this->setUserSession($user);

        // Update last login
        $this->updateLastLogin($user['id']);

        // Log successful login
        $this->logAudit($user['id'], 'login', 'User logged in successfully');

        return true;
    }

    /**
     * Log out the current user
     *
     * @return void
     */
    public function logout(): void
    {
        $userId = $this->getUserId();

        if ($userId) {
            $this->logAudit($userId, 'logout', 'User logged out');
        }

        $this->session->destroy();
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    public function check(): bool
    {
        return $this->session->has('user_id');
    }

    /**
     * Get current user ID
     *
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->session->get('user_id');
    }

    /**
     * Get current user data
     *
     * @return array|null
     */
    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }

        $userId = $this->getUserId();
        return $this->db->selectOne(
            "SELECT id, first_name, last_name, email, role, location_id, avatar FROM users WHERE id = ?",
            [$userId]
        );
    }

    /**
     * Check if user has a specific role
     *
     * @param string $role Role name
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        $user = $this->user();
        return $user && $user['role'] === $role;
    }

    /**
     * Check if user has any of the specified roles
     *
     * @param array $roles Array of role names
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        $user = $this->user();
        return $user && in_array($user['role'], $roles);
    }

    /**
     * Check if user has a specific permission
     *
     * @param string $permission Permission name
     * @return bool
     */
    public function hasPermission(string $permission): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        // Get role permissions
        $permissions = $this->getRolePermissions($user['role']);
        return in_array($permission, $permissions);
    }

    /**
     * Get permissions for a role
     *
     * @param string $role Role name
     * @return array
     */
    private function getRolePermissions(string $role): array
    {
        // Define role permissions
        $rolePermissions = [
            ROLE_ADMIN => [
                PERM_MANAGE_USERS,
                PERM_MANAGE_PATIENTS,
                PERM_VIEW_PATIENTS,
                PERM_MANAGE_EXAMS,
                PERM_VIEW_EXAMS,
                PERM_MANAGE_PRESCRIPTIONS,
                PERM_VIEW_PRESCRIPTIONS,
                PERM_MANAGE_APPOINTMENTS,
                PERM_VIEW_APPOINTMENTS,
                PERM_MANAGE_INVENTORY,
                PERM_VIEW_INVENTORY,
                PERM_MANAGE_POS,
                PERM_PROCESS_SALES,
                PERM_VIEW_REPORTS,
                PERM_MANAGE_SETTINGS,
                PERM_MANAGE_INSURANCE,
            ],
            ROLE_DOCTOR => [
                PERM_VIEW_PATIENTS,
                PERM_MANAGE_EXAMS,
                PERM_VIEW_EXAMS,
                PERM_MANAGE_PRESCRIPTIONS,
                PERM_VIEW_PRESCRIPTIONS,
                PERM_VIEW_APPOINTMENTS,
                PERM_VIEW_REPORTS,
            ],
            ROLE_OPTOMETRIST => [
                PERM_VIEW_PATIENTS,
                PERM_MANAGE_EXAMS,
                PERM_VIEW_EXAMS,
                PERM_MANAGE_PRESCRIPTIONS,
                PERM_VIEW_PRESCRIPTIONS,
                PERM_VIEW_APPOINTMENTS,
            ],
            ROLE_OPTICIAN => [
                PERM_VIEW_PATIENTS,
                PERM_VIEW_EXAMS,
                PERM_VIEW_PRESCRIPTIONS,
                PERM_VIEW_APPOINTMENTS,
                PERM_PROCESS_SALES,
                PERM_VIEW_INVENTORY,
            ],
            ROLE_RECEPTIONIST => [
                PERM_MANAGE_PATIENTS,
                PERM_VIEW_PATIENTS,
                PERM_MANAGE_APPOINTMENTS,
                PERM_VIEW_APPOINTMENTS,
            ],
            ROLE_MANAGER => [
                PERM_VIEW_PATIENTS,
                PERM_VIEW_EXAMS,
                PERM_VIEW_PRESCRIPTIONS,
                PERM_VIEW_APPOINTMENTS,
                PERM_MANAGE_INVENTORY,
                PERM_VIEW_INVENTORY,
                PERM_MANAGE_POS,
                PERM_PROCESS_SALES,
                PERM_VIEW_REPORTS,
                PERM_MANAGE_INSURANCE,
            ],
            ROLE_CASHIER => [
                PERM_PROCESS_SALES,
                PERM_VIEW_INVENTORY,
            ],
        ];

        return $rolePermissions[$role] ?? [];
    }

    /**
     * Hash a password
     *
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Verify password
     *
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Set user session data
     *
     * @param array $user User data
     * @return void
     */
    private function setUserSession(array $user): void
    {
        $this->session->set('user_id', $user['id']);
        $this->session->set('user_email', $user['email']);
        $this->session->set('user_role', $user['role']);
        $this->session->set('user_name', $user['first_name'] . ' ' . $user['last_name']);
        $this->session->regenerate();
    }

    /**
     * Check if account is locked
     *
     * @param string $email User email
     * @return bool
     */
    private function isAccountLocked(string $email): bool
    {
        $result = $this->db->selectOne(
            "SELECT COUNT(*) as attempts, MAX(created_at) as last_attempt
             FROM failed_login_attempts
             WHERE email = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$email, self::LOCKOUT_DURATION]
        );

        return $result && $result['attempts'] >= self::MAX_LOGIN_ATTEMPTS;
    }

    /**
     * Record failed login attempt
     *
     * @param string $email User email
     * @return void
     */
    private function recordFailedLogin(string $email): void
    {
        $this->db->insert('failed_login_attempts', [
            'email' => $email,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'created_at' => date(DATETIME_FORMAT),
        ]);
    }

    /**
     * Clear failed login attempts
     *
     * @param string $email User email
     * @return void
     */
    private function clearFailedLogins(string $email): void
    {
        $this->db->delete('failed_login_attempts', 'email = ?', [$email]);
    }

    /**
     * Update last login timestamp
     *
     * @param int $userId User ID
     * @return void
     */
    private function updateLastLogin(int $userId): void
    {
        $this->db->update(
            'users',
            ['last_login' => date(DATETIME_FORMAT)],
            'id = ?',
            [$userId]
        );
    }

    /**
     * Log audit event
     *
     * @param int $userId User ID
     * @param string $action Action performed
     * @param string $description Action description
     * @return void
     */
    private function logAudit(int $userId, string $action, string $description): void
    {
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
