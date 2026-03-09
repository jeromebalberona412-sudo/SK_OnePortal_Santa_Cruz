<?php

namespace App\Modules\Authentication\Services;

use Illuminate\Support\Facades\Http;

class TurnstileService
{
    public function verify(string $token, ?string $remoteIp = null): bool
    {
        $secretKey = (string) config('services.turnstile.secret_key', '');

        if ($token === '' || $secretKey === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post((string) config('services.turnstile.verify_url'), [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ]);

            if (! $response->ok()) {
                return false;
            }

            return (bool) data_get($response->json(), 'success', false);
        } catch (\Throwable) {
            return false;
        }
    }
}
