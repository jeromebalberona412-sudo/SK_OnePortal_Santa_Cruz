<?php

namespace App\Services;

use App\Models\BarangayZone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class BarangayZoneService
{
    public function activeZonesForBarangay(int $barangayId): Collection
    {
        if (! Schema::hasTable('barangay_zones')) {
            return collect();
        }

        return BarangayZone::query()
            ->where('barangay_id', $barangayId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'type']);
    }

    /**
     * @return list<string>
     */
    public function activeZoneNames(int $barangayId): array
    {
        return $this->activeZonesForBarangay($barangayId)
            ->pluck('name')
            ->all();
    }
}
