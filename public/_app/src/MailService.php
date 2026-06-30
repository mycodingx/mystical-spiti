<?php
/**
 * Mystical Expedition - PHPMailer Wrapper
 */

declare(strict_types=1);

namespace Mystical;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Throwable;

final class MailService
{
    /**
     * Send a "new lead" notification to the business inbox.
     */
    public function sendLeadNotification(array $lead): void
    {
        $to = Bootstrap::config('LEADS_NOTIFY_EMAIL', Bootstrap::config('BUSINESS_EMAIL'));
        if (!$to) {
            throw new \RuntimeException('LEADS_NOTIFY_EMAIL not configured.');
        }

        $businessName = Bootstrap::config('BUSINESS_NAME', 'Mystical Expedition');
        $businessPhone = Bootstrap::config('BUSINESS_PHONE', '');

        $subject = sprintf('[%s] New lead #%d — %s', $businessName, $lead['id'] ?? 0, $lead['destination']);

        $body = $this->renderEmail('new-lead', [
            'lead'          => $lead,
            'business_name' => $businessName,
            'business_phone' => $businessPhone,
        ]);

        $this->send($to, $subject, $body, $lead['email'] ?? null, $lead['name'] ?? null);
    }

    /**
     * Send an auto-reply confirmation to the user.
     */
    public function sendUserConfirmation(array $lead): void
    {
        if (empty($lead['email'])) {
            return;
        }

        $businessName = Bootstrap::config('BUSINESS_NAME', 'Mystical Expedition');
        $businessPhone = Bootstrap::config('BUSINESS_PHONE', '');
        $businessAddress = Bootstrap::config('BUSINESS_ADDRESS', '');

        $subject = sprintf('We received your enquiry — %s', $businessName);

        $body = $this->renderEmail('user-confirmation', [
            'lead'            => $lead,
            'business_name'   => $businessName,
            'business_phone'  => $businessPhone,
            'business_address' => $businessAddress,
        ]);

        $this->send($lead['email'], $subject, $body);
    }

    /**
     * Low-level send via PHPMailer + SMTP.
     */
    private function send(string $to, string $subject, string $htmlBody, ?string $replyToEmail = null, ?string $replyToName = null): void
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = Bootstrap::config('MAIL_HOST', 'smtp.hostinger.com');
            $mail->Port       = (int) Bootstrap::config('MAIL_PORT', '587');
            $mail->SMTPAuth   = true;
            $mail->Username   = Bootstrap::config('MAIL_USERNAME');
            $mail->Password   = Bootstrap::config('MAIL_PASSWORD');

            $enc = strtolower((string) Bootstrap::config('MAIL_ENCRYPTION', 'tls'));
            if ($enc === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } elseif ($enc === 'tls') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $fromAddr = Bootstrap::config('MAIL_FROM_ADDRESS', 'noreply@example.com');
            $fromName = Bootstrap::config('MAIL_FROM_NAME', 'Mystical Expedition');

            $mail->setFrom($fromAddr, $fromName);
            $mail->addAddress($to);

            if ($replyToEmail) {
                $mail->addReplyTo($replyToEmail, $replyToName ?? $replyToEmail);
            }

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

            $mail->send();
        } catch (PHPMailerException $e) {
            throw new \RuntimeException('Mail send failed: ' . $mail->ErrorInfo, 0, $e);
        } catch (Throwable $e) {
            throw new \RuntimeException('Mail send failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Render an HTML email template from src/Emails/<filename>.php
     * Variables passed in $vars are exposed to the template scope.
     */
    private function renderEmail(string $template, array $vars): string
    {
        $file = __DIR__ . '/Emails/' . $template . '.php';
        if (!file_exists($file)) {
            return '<p>(Template missing)</p>';
        }

        extract($vars, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }
}