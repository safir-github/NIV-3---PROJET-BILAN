<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Class Mailer
 * 
 * Simple mailer service handling account activation and transactional emails.
 * Supports standard PHP mail() or local SMTP testing setups (e.g., Mailpit/Mailtrap).
 * 
 * @package App\Services
 */
class Mailer
{
    /**
     * Send account activation email with unique activation URL.
     *
     * @param string $recipientEmail
     * @param string $firstName
     * @param string $activationToken
     * @return bool
     */
    public function sendActivationEmail(string $recipientEmail, string $firstName, string $activationToken): bool
    {
        $appUrl = $_ENV['APP_URL'] ?? 'http://localhost:8000';
        $activationLink = sprintf('%s/activate?token=%s', rtrim($appUrl, '/'), urlencode($activationToken));

        $subject = 'Activez votre compte Knowledge Learning';
        $message = "Bonjour " . htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') . ",\n\n"
                 . "Merci pour votre inscription sur la plateforme Knowledge Learning.\n"
                 . "Pour activer votre compte et débloquer vos formations, cliquez sur le lien suivant :\n\n"
                 . $activationLink . "\n\n"
                 . "Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email.\n\n"
                 . "L'équipe Knowledge Learning";

        $headers = [
            'From' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@knowledge.local',
            'Reply-To' => $_ENV['MAIL_FROM_ADDRESS'] ?? 'no-reply@knowledge.local',
            'X-Mailer' => 'PHP/' . phpversion(),
            'Content-Type' => 'text/plain; charset=UTF-8'
        ];

        // Format headers for mail()
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "$key: $value\r\n";
        }

        // Send using native PHP mail function
        // Returns false gracefully in local dev environments if sendmail is not configured
        return @mail($recipientEmail, $subject, $message, $headerString);
    }
}
