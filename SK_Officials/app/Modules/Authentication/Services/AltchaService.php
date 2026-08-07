<?php

namespace App\Modules\Authentication\Services;

use AltchaOrg\Altcha\Algorithm\DeriveKeyInterface;
use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Challenge;
use AltchaOrg\Altcha\CreateChallengeOptions;
use AltchaOrg\Altcha\VerifySolutionOptions;
use Illuminate\Support\Facades\Cache;

class AltchaService
{
    protected ?Altcha $client = null;

    public function enabled(): bool
    {
        if (! config('altcha.enabled', true)) {
            return false;
        }

        return $this->hmacSecret() !== '';
    }

    public function challengeUrl(): string
    {
        return route('sk_official.altcha.challenge', [], false);
    }

    public function createChallenge(): Challenge
    {
        $expiresIn = max(60, (int) config('altcha.expires_in', 600));

        return $this->client()->createChallenge(new CreateChallengeOptions(
            algorithm: $this->algorithm(),
            cost: max(1000, (int) config('altcha.cost', 5000)),
            expiresAt: time() + $expiresIn,
        ));
    }

    public function verify(?string $payload): AltchaVerificationResult
    {
        if (! $this->enabled()) {
            return AltchaVerificationResult::success();
        }

        $payload = trim((string) $payload);

        if ($payload === '') {
            return AltchaVerificationResult::missing();
        }

        try {
            $result = $this->client()->verifySolution(new VerifySolutionOptions(
                algorithm: $this->algorithm(),
                payload: $payload,
            ));
        } catch (\Throwable) {
            return AltchaVerificationResult::invalid();
        }

        if ($result->expired) {
            return AltchaVerificationResult::expired();
        }

        if (! $result->verified) {
            return AltchaVerificationResult::invalid();
        }

        if ($this->isReplay($payload)) {
            return AltchaVerificationResult::replay();
        }

        $this->markUsed($payload);

        return AltchaVerificationResult::success();
    }

    protected function isReplay(string $payload): bool
    {
        return $this->cache()->has($this->replayCacheKey($payload));
    }

    protected function markUsed(string $payload): void
    {
        $ttl = max(60, (int) config('altcha.replay_cache_ttl', 900));

        $this->cache()->put(
            $this->replayCacheKey($payload),
            true,
            $ttl,
        );
    }

    protected function cache(): \Illuminate\Contracts\Cache\Repository
    {
        $store = $this->cacheStore();

        return $store !== null ? Cache::store($store) : Cache::store();
    }

    protected function replayCacheKey(string $payload): string
    {
        return 'sk_official_altcha:used:'.hash('sha256', $payload);
    }

    protected function cacheStore(): ?string
    {
        $store = config('altcha.replay_cache_store');

        return is_string($store) && $store !== '' ? $store : null;
    }

    protected function hmacSecret(): string
    {
        return (string) config('altcha.hmac_secret', '');
    }

    protected function hmacKeySecret(): ?string
    {
        $secret = (string) config('altcha.hmac_key_secret', '');

        return $secret !== '' ? $secret : null;
    }

    protected function algorithm(): DeriveKeyInterface
    {
        return match (strtolower((string) config('altcha.algorithm', 'pbkdf2'))) {
            default => new Pbkdf2(),
        };
    }

    protected function client(): Altcha
    {
        if ($this->client === null) {
            $this->client = new Altcha(
                hmacSignatureSecret: $this->hmacSecret(),
                hmacKeySignatureSecret: $this->hmacKeySecret(),
            );
        }

        return $this->client;
    }
}
