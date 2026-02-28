<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Shared\Models\User;

class DeviceVerificationResult
{
    public function __construct(
        public bool $verified,
        public ?User $user,
    ) {}
}
