<?php

namespace App\Modules\Authentication\Services;

class FeatureFlagService
{
    public function isEnabled(string $flag): bool
    {
        $flags = (array) config('sk_official_auth.feature_flags', []);

        return (bool) ($flags[$flag] ?? false);
    }

    public function deviceVerificationEnabled(): bool
    {
        return $this->isEnabled('features.device_verification');
    }

    public function loginAlertNotificationsEnabled(): bool
    {
        return $this->isEnabled('features.login_alert_notifications');
    }

    public function suspiciousLoginDetectionEnabled(): bool
    {
        return $this->isEnabled('features.suspicious_login_detection');
    }
}
