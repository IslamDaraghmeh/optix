<?php
/**
 * Email Helper Class
 *
 * PHPMailer wrapper for sending emails
 *
 * @package App\Helpers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    /**
     * @var PHPMailer PHPMailer instance
     */
    private PHPMailer $mailer;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    /**
     * Configure PHPMailer with settings from config
     *
     * @return void
     */
    private function configure(): void
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = MAIL_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = MAIL_USERNAME;
            $this->mailer->Password = MAIL_PASSWORD;
            $this->mailer->SMTPSecure = MAIL_ENCRYPTION;
            $this->mailer->Port = MAIL_PORT;

            // Default sender
            $this->mailer->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

            // Character encoding
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            $this->logError('Email configuration failed: ' . $e->getMessage());
        }
    }

    /**
     * Send email
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string|null $altBody Plain text alternative
     * @param array $attachments File attachments
     * @return bool
     */
    public function send(string $to, string $subject, string $body, ?string $altBody = null, array $attachments = []): bool
    {
        try {
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->isHTML(true);
            $this->mailer->Body = $body;

            if ($altBody) {
                $this->mailer->AltBody = $altBody;
            }

            // Add attachments
            foreach ($attachments as $attachment) {
                if (is_array($attachment)) {
                    $this->mailer->addAttachment($attachment['path'], $attachment['name'] ?? '');
                } else {
                    $this->mailer->addAttachment($attachment);
                }
            }

            $result = $this->mailer->send();

            // Log successful email
            $this->logEmail($to, $subject, 'sent');

            // Clear recipients and attachments for next email
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            return $result;
        } catch (Exception $e) {
            $this->logError('Email sending failed: ' . $e->getMessage());
            $this->logEmail($to, $subject, 'failed', $e->getMessage());
            return false;
        }
    }

    /**
     * Send appointment reminder email
     *
     * @param string $to Patient email
     * @param array $appointment Appointment data
     * @return bool
     */
    public function sendAppointmentReminder(string $to, array $appointment): bool
    {
        $subject = 'Appointment Reminder - ' . APP_NAME;
        $body = $this->getAppointmentReminderTemplate($appointment);
        return $this->send($to, $subject, $body);
    }

    /**
     * Send appointment confirmation email
     *
     * @param string $to Patient email
     * @param array $appointment Appointment data
     * @return bool
     */
    public function sendAppointmentConfirmation(string $to, array $appointment): bool
    {
        $subject = 'Appointment Confirmation - ' . APP_NAME;
        $body = $this->getAppointmentConfirmationTemplate($appointment);
        return $this->send($to, $subject, $body);
    }

    /**
     * Send receipt email
     *
     * @param string $to Customer email
     * @param array $transaction Transaction data
     * @param string $pdfPath PDF receipt path
     * @return bool
     */
    public function sendReceipt(string $to, array $transaction, string $pdfPath = ''): bool
    {
        $subject = 'Receipt - ' . APP_NAME;
        $body = $this->getReceiptTemplate($transaction);

        $attachments = [];
        if ($pdfPath && file_exists($pdfPath)) {
            $attachments[] = ['path' => $pdfPath, 'name' => 'receipt.pdf'];
        }

        return $this->send($to, $subject, $body, null, $attachments);
    }

    /**
     * Send password reset email
     *
     * @param string $to User email
     * @param string $resetToken Reset token
     * @param string $userName User name
     * @return bool
     */
    public function sendPasswordReset(string $to, string $resetToken, string $userName): bool
    {
        $subject = 'Password Reset Request - ' . APP_NAME;
        $body = $this->getPasswordResetTemplate($resetToken, $userName);
        return $this->send($to, $subject, $body);
    }

    /**
     * Send welcome email
     *
     * @param string $to User email
     * @param string $userName User name
     * @return bool
     */
    public function sendWelcome(string $to, string $userName): bool
    {
        $subject = 'Welcome to ' . APP_NAME;
        $body = $this->getWelcomeTemplate($userName);
        return $this->send($to, $subject, $body);
    }

    /**
     * Get appointment reminder email template
     *
     * @param array $appointment Appointment data
     * @return string
     */
    private function getAppointmentReminderTemplate(array $appointment): string
    {
        $date = date('l, F j, Y', strtotime($appointment['date']));
        $time = date('g:i A', strtotime($appointment['time']));

        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <h2 style='color: #2c3e50;'>Appointment Reminder</h2>
            <p>Dear {$appointment['patient_name']},</p>
            <p>This is a friendly reminder of your upcoming appointment:</p>
            <div style='background-color: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <strong>Date:</strong> {$date}<br>
                <strong>Time:</strong> {$time}<br>
                <strong>Provider:</strong> {$appointment['provider_name']}<br>
                <strong>Type:</strong> {$appointment['type']}
            </div>
            <p>Please arrive 15 minutes early to complete any necessary paperwork.</p>
            <p>If you need to reschedule or cancel, please contact us at least 24 hours in advance.</p>
            <p>Thank you,<br>" . APP_NAME . "</p>
        </body>
        </html>
        ";
    }

    /**
     * Get appointment confirmation email template
     *
     * @param array $appointment Appointment data
     * @return string
     */
    private function getAppointmentConfirmationTemplate(array $appointment): string
    {
        $date = date('l, F j, Y', strtotime($appointment['date']));
        $time = date('g:i A', strtotime($appointment['time']));

        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <h2 style='color: #27ae60;'>Appointment Confirmed</h2>
            <p>Dear {$appointment['patient_name']},</p>
            <p>Your appointment has been confirmed:</p>
            <div style='background-color: #e8f8f5; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <strong>Date:</strong> {$date}<br>
                <strong>Time:</strong> {$time}<br>
                <strong>Provider:</strong> {$appointment['provider_name']}<br>
                <strong>Type:</strong> {$appointment['type']}
            </div>
            <p>We look forward to seeing you!</p>
            <p>Thank you,<br>" . APP_NAME . "</p>
        </body>
        </html>
        ";
    }

    /**
     * Get receipt email template
     *
     * @param array $transaction Transaction data
     * @return string
     */
    private function getReceiptTemplate(array $transaction): string
    {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <h2 style='color: #2c3e50;'>Receipt</h2>
            <p>Dear Customer,</p>
            <p>Thank you for your purchase. Please find your receipt attached.</p>
            <div style='background-color: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                <strong>Transaction ID:</strong> {$transaction['id']}<br>
                <strong>Date:</strong> " . date('F j, Y', strtotime($transaction['created_at'])) . "<br>
                <strong>Total:</strong> $" . number_format($transaction['total'], 2) . "
            </div>
            <p>We appreciate your business!</p>
            <p>Thank you,<br>" . APP_NAME . "</p>
        </body>
        </html>
        ";
    }

    /**
     * Get password reset email template
     *
     * @param string $resetToken Reset token
     * @param string $userName User name
     * @return string
     */
    private function getPasswordResetTemplate(string $resetToken, string $userName): string
    {
        $resetUrl = APP_URL . '/reset-password?token=' . $resetToken;

        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <h2 style='color: #2c3e50;'>Password Reset Request</h2>
            <p>Dear {$userName},</p>
            <p>We received a request to reset your password. Click the button below to reset it:</p>
            <div style='margin: 30px 0; text-align: center;'>
                <a href='{$resetUrl}' style='background-color: #3498db; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a>
            </div>
            <p>If you didn't request this, please ignore this email.</p>
            <p>This link will expire in 1 hour.</p>
            <p>Thank you,<br>" . APP_NAME . "</p>
        </body>
        </html>
        ";
    }

    /**
     * Get welcome email template
     *
     * @param string $userName User name
     * @return string
     */
    private function getWelcomeTemplate(string $userName): string
    {
        return "
        <html>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <h2 style='color: #27ae60;'>Welcome to " . APP_NAME . "!</h2>
            <p>Dear {$userName},</p>
            <p>Welcome! We're excited to have you on board.</p>
            <p>You can now access your account and schedule appointments online.</p>
            <p>If you have any questions, please don't hesitate to contact us.</p>
            <p>Thank you,<br>" . APP_NAME . "</p>
        </body>
        </html>
        ";
    }

    /**
     * Log email activity
     *
     * @param string $to Recipient
     * @param string $subject Subject
     * @param string $status Status (sent/failed)
     * @param string|null $error Error message if failed
     * @return void
     */
    private function logEmail(string $to, string $subject, string $status, ?string $error = null): void
    {
        $logMessage = sprintf(
            "[%s] Email %s to %s - Subject: %s",
            date(DATETIME_FORMAT),
            $status,
            $to,
            $subject
        );

        if ($error) {
            $logMessage .= " - Error: {$error}";
        }

        error_log($logMessage . "\n", 3, LOG_PATH . '/email.log');
    }

    /**
     * Log error
     *
     * @param string $message Error message
     * @return void
     */
    private function logError(string $message): void
    {
        error_log('[Email ERROR] ' . $message);
    }
}
