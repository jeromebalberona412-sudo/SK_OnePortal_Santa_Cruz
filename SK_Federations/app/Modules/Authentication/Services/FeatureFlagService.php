<?php

namespace App\Modules\Authentication\Services;

use App\Modules\Authentication\Models\FeatureFlag;

class FeatureFlagService
{
    public function enabled(string $key): bool
    {
        $default = (bool) data_get(config('sk_fed_auth.feature_flags'), $key, false);

        $flag = FeatureFlag::query()->where('flag_key', $key)->first();

        if ($flag === null) {
            return $default;
        }

        return (bool) $flag->enabled;
    }

    public function deviceVerificationEnabled(): bool
    {
        return $this->enabled('features.device_verification');
    }
}
