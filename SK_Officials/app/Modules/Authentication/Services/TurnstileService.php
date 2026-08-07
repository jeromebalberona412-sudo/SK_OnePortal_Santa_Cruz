<?php

namespace App\Modules\Authentication\Services;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Facades\Http;

class TurnstileService
{
    protected string $secretKey;
    protected string $verifyUrl = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct()
    {
        $this->secretKey = (string) config('services.turnstile.secret_key', '');
    }

    public function verify(string $token, ?string $remoteIp = null): bool
    {
        if ($this->secretKey === '' || $this->secretKey === '1x0000000000000000000000000000000AA') {
            // Dev/test bypass key — always passes
            return true;
        }

        try {
            $payload = [
                'secret'   => $this->secretKey,
                'response' => $token,
            ];

            if ($remoteIp !== null) {
                $payload['remoteip'] = $remoteIp;
            }

            $response = Http::asForm()->post($this->verifyUrl, $payload);

            $success = (bool) ($response->json('success', false));

            // Log the full response when verification fails (for debugging)
            if (!$success) {
                \Log::warning('Turnstile verification failed', [
                    'response' => $response->json(),
                    'remote_ip' => $remoteIp,
                    'token_length' => strlen($token),
                ]);
            }

            return $success;
        } catch (\Throwable $e) {
            \Log::error('Turnstile verification exception', [
                'message' => $e->getMessage(),
                'remote_ip' => $remoteIp,
            ]);
            return false;
        }
    }
}
