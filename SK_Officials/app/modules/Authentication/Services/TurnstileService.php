<?php

namespace App\Modules\Authentication\Services;

use Illuminate\Support\Facades\Http;

class TurnstileService
{
    public function verify(string $responseToken, ?string $remoteIp = null): bool
    {
        if (! (bool) config('services.turnstile.enabled', false)) {
            return true;
        }

        $secretKey = (string) config('services.turnstile.secret_key', '');

        if ($secretKey === '' || $responseToken === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post((string) config('services.turnstile.verify_url'), [
                    'secret' => $secretKey,
                    'response' => $responseToken,
                    'remoteip' => $remoteIp,
                ]);
        } catch (\Throwable) {
            return false;
        }

        return (bool) data_get($response->json(), 'success', false);
    }
}
