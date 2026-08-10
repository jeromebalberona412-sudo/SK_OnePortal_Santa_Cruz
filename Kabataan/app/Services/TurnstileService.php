<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    protected bool $enabled;
    protected string $siteKey;
    protected string $secretKey;
    protected string $verifyUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->enabled = (bool) config('services.turnstile.enabled', true);
        $this->siteKey = (string) config('services.turnstile.site_key', '');
        $this->secretKey = (string) config('services.turnstile.secret_key', '');
        $this->verifyUrl = (string) config('services.turnstile.verify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
        $this->timeout = (int) config('services.turnstile.timeout', 10);
    }

    /**
     * Check if Turnstile verification is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled && $this->siteKey !== '' && $this->secretKey !== '';
    }

    /**
     * Get the configured site key.
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Verify a Turnstile token with Cloudflare's API.
     *
     * @param  string  $token  The cf-turnstile-response token from the frontend.
     * @param  string|null  $remoteIp  Optional client IP address.
     * @return bool True if verification succeeds, false otherwise.
     */
    public function verify(string $token, ?string $remoteIp = null): bool
    {
        // If Turnstile is disabled or unconfigured, bypass verification
        if (!$this->isEnabled()) {
            Log::info('Turnstile verification bypassed (disabled or keys unconfigured)');
            return true;
        }

        // If site key or secret key is dev/test bypass key, allow
        if ($this->siteKey === '1x0000000000000000000000000000000AA' || $this->secretKey === '1x0000000000000000000000000000000AA') {
            Log::info('Turnstile verification bypassed (dev/test key)');
            return true;
        }

        // If token is empty, reject immediately
        if (trim($token) === '') {
            Log::warning('Turnstile verification failed: empty token');
            return false;
        }

        try {
            $payload = [
                'secret'   => $this->secretKey,
                'response' => $token,
            ];

            if ($remoteIp !== null) {
                $payload['remoteip'] = $remoteIp;
            }

            $response = Http::timeout($this->timeout)
                ->asForm()
                ->post($this->verifyUrl, $payload);

            if (!$response->successful()) {
                Log::error('Turnstile verification HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return false;
            }

            $data = $response->json();
            $success = (bool) ($data['success'] ?? false);

            if (!$success) {
                Log::warning('Turnstile verification failed', [
                    'error_codes' => $data['error-codes'] ?? [],
                    'remote_ip' => $remoteIp,
                    'token_length' => strlen($token),
                ]);
            } else {
                Log::info('Turnstile verification successful');
            }

            return $success;
        } catch (ConnectionException $e) {
            Log::error('Turnstile verification connection exception', [
                'message' => $e->getMessage(),
                'remote_ip' => $remoteIp,
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Turnstile verification exception', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
                'remote_ip' => $remoteIp,
            ]);
            return false;
        }
    }
}
