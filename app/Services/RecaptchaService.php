<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    /**
     * Whether reCAPTCHA is configured (keys present).
     */
    public static function enabled(): bool
    {
        return (bool) (SiteSetting::get('recaptcha_site_key') && SiteSetting::get('recaptcha_secret_key'));
    }

    public static function siteKey(): ?string
    {
        return SiteSetting::get('recaptcha_site_key');
    }

    /**
     * Verify a reCAPTCHA v3/v2 token. Returns true when disabled (so forms still work
     * before keys are configured) or when verification passes.
     */
    public static function verify(?string $token, ?string $ip = null): bool
    {
        if (!self::enabled()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(10)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => SiteSetting::get('recaptcha_secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            $data = $response->json();

            // For v3, also enforce a minimum score when present.
            if (isset($data['score'])) {
                return ($data['success'] ?? false) && $data['score'] >= 0.5;
            }

            return $data['success'] ?? false;
        } catch (\Throwable $e) {
            // Fail open on network errors to avoid blocking genuine leads.
            return true;
        }
    }
}
