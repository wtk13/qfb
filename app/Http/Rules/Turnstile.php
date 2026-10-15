<?php

namespace App\Http\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifies a Cloudflare Turnstile token server-side.
 */
class Turnstile implements ValidationRule
{
    public static function enabled(): bool
    {
        return filled(config('services.turnstile.site_key')) && filled(config('services.turnstile.secret_key'));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);
        } catch (ConnectionException $e) {
            Log::warning('Turnstile siteverify unreachable', ['error' => $e->getMessage()]);
            $fail(__('auth.turnstile_failed'));

            return;
        }

        if ($response->json('success') !== true) {
            Log::info('Turnstile verification failed', ['error_codes' => $response->json('error-codes')]);
            $fail(__('auth.turnstile_failed'));
        }
    }
}
