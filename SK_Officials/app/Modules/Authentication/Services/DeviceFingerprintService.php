<?php

namespace App\Modules\Authentication\Services;

use Illuminate\Http\Request;

class DeviceFingerprintService
{
    /**
     * Generate a stable fingerprint for the current request's device.
     */
    public function fingerprint(Request $request): string
    {
        $components = [
            $request->userAgent() ?? '',
            $request->header('Accept-Language') ?? '',
            $request->header('Accept-Encoding') ?? '',
        ];

        return hash('sha256', implode('|', $components));
    }
}
