<?php

namespace App\Modules\Authentication\Services;

class AltchaVerificationResult
{
    public const REASON_MISSING = 'missing';

    public const REASON_EXPIRED = 'expired';

    public const REASON_INVALID = 'invalid';

    public const REASON_REPLAY = 'replay';

    public function __construct(
        public readonly bool $verified,
        public readonly ?string $reason = null,
    ) {}

    public static function success(): self
    {
        return new self(true);
    }

    public static function missing(): self
    {
        return new self(false, self::REASON_MISSING);
    }

    public static function expired(): self
    {
        return new self(false, self::REASON_EXPIRED);
    }

    public static function invalid(): self
    {
        return new self(false, self::REASON_INVALID);
    }

    public static function replay(): self
    {
        return new self(false, self::REASON_REPLAY);
    }

    public function message(): string
    {
        return match ($this->reason) {
            self::REASON_MISSING => 'Please complete the security verification.',
            default => 'Security verification failed. Please try again.',
        };
    }
}
