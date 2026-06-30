<?php
/**
 * Mystical Expedition - Lead Validation & Persistence
 */

declare(strict_types=1);

namespace Mystical;

final class LeadService
{
    /** @var array<string,string> Field-level errors */
    private array $errors = [];

    /**
     * Process an incoming lead submission.
     *
     * @return array{ok:bool, lead?:array, errors?:array, message?:string}
     */
    public function process(array $input, string $ip, string $userAgent, string $referrer): array
    {
        // 1. Honeypot — must be empty
        if (!empty($input['website'])) {
            // Pretend success to bot
            return ['ok' => true, 'message' => 'Thank you!'];
        }

        // 2. Rate limit
        $limit = (int) (Bootstrap::config('LEADS_RATE_LIMIT_PER_HOUR', '5'));
        $recent = Database::countRecentByIp($ip, 3600);
        if ($recent >= $limit) {
            return [
                'ok'      => false,
                'errors'  => ['_form' => 'Too many submissions. Please try again later.'],
                'message' => 'Too many submissions. Please try again later.',
            ];
        }

        // 3. Validate fields
        $clean = $this->validate($input);
        if (!empty($this->errors)) {
            return [
                'ok'      => false,
                'errors'  => $this->errors,
                'message' => 'Please correct the highlighted fields.',
            ];
        }

        // 4. Persist
        $clean['ip_address'] = $ip;
        $clean['user_agent'] = substr($userAgent, 0, 500);
        $clean['referrer']   = substr($referrer, 0, 500);
        $clean['status']     = 'new';

        try {
            $id = Database::insertLead($clean);
            $clean['id'] = $id;
        } catch (\Throwable $e) {
            return [
                'ok'      => false,
                'errors'  => ['_form' => 'Could not save your request. Please try again.'],
                'message' => 'Could not save your request.',
            ];
        }

        // 5. Send email (best effort - don't fail the lead if mail fails)
        $mailError = null;
        try {
            $mailer = new MailService();
            $mailer->sendLeadNotification($clean);
        } catch (\Throwable $e) {
            $mailError = $e->getMessage();
        }

        return [
            'ok'         => true,
            'lead'       => $clean,
            'mail_error' => $mailError,
            'message'    => 'Thank you! Our travel expert will contact you shortly.',
        ];
    }

    /**
     * Validate and sanitise input. Returns cleaned array on success.
     */
    private function validate(array $input): array
    {
        $clean = [];

        // Name
        $name = trim((string) ($input['name'] ?? ''));
        if (strlen($name) < 2 || strlen($name) > 100) {
            $this->errors['name'] = 'Please enter your full name (2-100 characters).';
        } elseif (!preg_match("/^[\p{L}\p{M}\s'\-\.]+$/u", $name)) {
            $this->errors['name'] = 'Name contains invalid characters.';
        } else {
            $clean['name'] = $name;
        }

        // City
        $city = trim((string) ($input['city'] ?? ''));
        if (strlen($city) < 2 || strlen($city) > 60) {
            $this->errors['city'] = 'Please enter your city (2-60 characters).';
        } else {
            $clean['city'] = $city;
        }

        // Email
        $email = trim((string) ($input['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'Please enter a valid email address.';
        } else {
            $clean['email'] = strtolower($email);
        }

        // Phone (Indian mobile)
        $phone = preg_replace('/\D+/', '', (string) ($input['phone'] ?? ''));
        if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
            $this->errors['phone'] = 'Please enter a valid 10-digit Indian mobile number.';
        } else {
            $clean['phone'] = $phone;
        }

        // Destination - whitelist against packages.json
        $dest = trim((string) ($input['destination'] ?? ''));
        $allowed = $this->allowedDestinations();
        if ($dest === '' || !in_array($dest, $allowed, true)) {
            $this->errors['destination'] = 'Please select a valid destination.';
        } else {
            $clean['destination'] = $dest;
        }

        // Optional message
        if (isset($input['message'])) {
            $msg = trim((string) $input['message']);
            if (strlen($msg) > 1000) {
                $msg = substr($msg, 0, 1000);
            }
            $clean['message'] = $msg;
        }

        return $clean;
    }

    /**
     * Load valid destinations from data/packages.json.
     *
     * @return string[]
     */
    private function allowedDestinations(): array
    {
        $file = Bootstrap::projectRoot() . '/data/packages.json';
        if (!file_exists($file)) {
            return [];
        }
        $json = file_get_contents($file);
        if ($json === false) {
            return [];
        }
        $packages = json_decode($json, true);
        if (!is_array($packages)) {
            return [];
        }
        $titles = array_column($packages, 'title');
        $titles = array_map(static fn($t) => trim((string) $t), $titles);
        return $titles;
    }
}