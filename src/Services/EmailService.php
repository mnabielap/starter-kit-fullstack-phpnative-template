<?php

namespace App\Services;

use App\Utils\Helper;

class EmailService
{
    /**
     * Send email
     */
    public function sendEmail(string $to, string $subject, string $text)
    {
        $from = Helper::env('EMAIL_FROM', 'noreply@app.com');
        $headers = "From: " . $from . "\r\n" .
                   "Reply-To: " . $from . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        // Use PHP native mail (In production, use PHPMailer or similar)
        // For local development without SMTP, this might fail or do nothing.
        // We log it for dev purposes.
        
        if (Helper::env('APP_ENV') === 'development') {
             error_log("--- MOCK EMAIL ---");
             error_log("To: $to");
             error_log("Subject: $subject");
             error_log("Body: $text");
             error_log("------------------");
        }
        
        // mail($to, $subject, $text, $headers);
    }

    public function sendResetPasswordEmail(string $to, string $token)
    {
        $subject = 'Reset password';
        $resetPasswordUrl = Helper::baseUrl("reset-password?token={$token}");
        $text = "Dear user,\nTo reset your password, click on this link: {$resetPasswordUrl}\nIf you did not request any password resets, then ignore this email.";
        
        $this->sendEmail($to, $subject, $text);
    }

    public function sendVerificationEmail(string $to, string $token)
    {
        $subject = 'Email Verification';
        $verificationEmailUrl = Helper::baseUrl("verify-email?token={$token}");
        $text = "Dear user,\nTo verify your email, click on this link: {$verificationEmailUrl}\nIf you did not create an account, then ignore this email.";
        
        $this->sendEmail($to, $subject, $text);
    }
}