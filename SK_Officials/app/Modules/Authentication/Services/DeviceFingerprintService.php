<?php

namespace App\Modules\Authentication\Services;

use Illuminate\Http\Request;

class DeviceFingerprintService
{
    public function fromRequest(Request $request): string
    {
        return hash('sha256', implode('|', [
            (string) $request->ip(),
            (string) ($request->userAgent() ?? ''),
            (string) $request->headers->get('accept-language', ''),
        ]));
    }
}
