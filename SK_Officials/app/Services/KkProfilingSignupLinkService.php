<?php

namespace App\Services;

use App\Models\Barangay;

class KkProfilingSignupLinkService
{
    public function forBarangay(?Barangay $barangay, ?string $appUrl = null): ?string
    {
        $name = trim((string) ($barangay?->name ?? ''));

        if ($name === '') {
            return null;
        }

        $base = rtrim((string) ($appUrl ?? config('services.kabataan_app_url', 'http://localhost:8002')), '/');

        return $base.'/kkprofiling/signup/'.$this->slugFromName($name);
    }

    public function slugFromName(string $name): string
    {
        $slug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $name));

        return trim($slug, '-');
    }
}
