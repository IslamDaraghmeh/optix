<?php
/**
 * Auth Controller
 *
 * Handles user authentication (login, logout, password reset)
 *
 * @package App\Controllers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Controllers;

use App\Helpers\Email;

class AuthController extends BaseController
{
    /**
     * Show login form
     *
     * @return void
     */
    public function showLoginForm(): void
    {
        // Redirect if already authenticated
        if ($this->isAuthenticated()) {
            $this->redirect(APP_URL . '/dashboard');
        }

        $this->view('auth/login');
    }

    /**
     * Process login
     *
     * @return void
     */
    public function login(): void
    {
        error_log("=== LOGIN METHOD CALLED ===");

        if (!$this->isPost()) {
            error_log("Not a POST request, redirecting...");
            $this->redirect(APP_URL . '/login');
        }

        error_log("POST request received");

        // Temporarily disabled for testing
        // $this->requireCsrfToken();

        $email = $this->post('email');
        $password = $this->post('password');

        error_log("Email: " . $email);
        error_log("Password length: " . strlen($password));

        // Validate input
        if (empty($email) || empty($password)) {
            error_log("Empty email or password");
            $this->flashAndRedirect('error', 'Email and password are required', APP_URL . '/login');
        }

        error_log("Attempting login...");
        // Attempt login
        if ($this->auth->login($email, $password)) {
            error_log("Login SUCCESS! Redirecting to dashboard");
            $this->redirect(APP_URL . '/dashboard');
        } else {
            error_log("Login FAILED!");
            $this->flashAndRedirect('error', 'Invalid email or password', APP_URL . '/login');
        }
    }

    /**
     * Logout
     *
     * @return void
     */
    public function logout(): void
    {
        $this->auth->logout();
        $this->flashAndRedirect('success', 'You have been logged out', APP_URL . '/login');
    }

    /**
     * Show forgot password form
     *
     * @return void
     */
    public function showForgotPasswordForm(): void
    {
        $this->view('auth/forgot-password');
    }

    /**
     * Send password reset link
     *
     * @return void
     */
    public function sendResetLink(): void
    {
        if (!$this->isPost()) {
            $this->redirect(APP_URL . '/forgot-password');
        }

        $this->requireCsrfToken();

        $email = $this->post('email');

        if (empty($email)) {
            $this->flashAndRedirect('error', 'Email is required', APP_URL . '/forgot-password');
        }

        // Check if user exists
        $user = $this->db->selectOne(
            "SELECT * FROM users WHERE email = ? AND deleted_at IS NULL",
            [$email]
        );

        if (!$user) {
            // Don't reveal if user exists
            $this->flashAndRedirect('success', 'If an account exists with that email, a password reset link has been sent', APP_URL . '/forgot-password');
        }

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date(DATETIME_FORMAT, strtotime('+1 hour'));

        // Store token in database (you would need a password_resets table)
        $this->db->insert('password_resets', [
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiry,
            'created_at' => date(DATETIME_FORMAT),
        ]);

        // Send email
        $emailHelper = new Email();
        $userName = $user['first_name'] . ' ' . $user['last_name'];
        $emailHelper->sendPasswordReset($email, $token, $userName);

        $this->flashAndRedirect('success', 'Password reset link has been sent to your email', APP_URL . '/forgot-password');
    }

    /**
     * Show reset password form
     *
     * @param string $token Reset token
     * @return void
     */
    public function showResetPasswordForm(string $token): void
    {
        // Verify token
        $reset = $this->db->selectOne(
            "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()",
            [$token]
        );

        if (!$reset) {
            $this->flashAndRedirect('error', 'Invalid or expired reset token', APP_URL . '/login');
        }

        $this->view('auth/reset-password', ['token' => $token]);
    }

    /**
     * Process password reset
     *
     * @return void
     */
    public function resetPassword(): void
    {
        if (!$this->isPost()) {
            $this->redirect(APP_URL . '/login');
        }

        $this->requireCsrfToken();

        $token = $this->post('token');
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');

        // Validate
        if ($password !== $passwordConfirm) {
            $this->flashAndRedirect('error', 'Passwords do not match', APP_URL . '/reset-password/' . $token);
        }

        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $this->flashAndRedirect('error', 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters', APP_URL . '/reset-password/' . $token);
        }

        // Verify token
        $reset = $this->db->selectOne(
            "SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()",
            [$token]
        );

        if (!$reset) {
            $this->flashAndRedirect('error', 'Invalid or expired reset token', APP_URL . '/login');
        }

        // Update password
        $hashedPassword = $this->auth->hashPassword($password);
        $this->db->update(
            'users',
            ['password' => $hashedPassword],
            'email = ?',
            [$reset['email']]
        );

        // Delete reset token
        $this->db->delete('password_resets', 'token = ?', [$token]);

        $this->flashAndRedirect('success', 'Password has been reset successfully. You can now log in', APP_URL . '/login');
    }
}
