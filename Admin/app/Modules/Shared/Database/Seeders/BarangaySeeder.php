<?php

namespace App\Modules\Shared\Database\Seeders;

use App\Modules\Shared\Models\Barangay;
use App\Modules\Shared\Models\Tenant;
use Illuminate\Database\Seeder;

class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('code', 'santa_cruz')->first();

        if (! $tenant) {
            return;
        }

        self::seedTenant($tenant);
    }

    public static function seedTenant(Tenant $tenant): void
    {
        $barangays = [
            'Barangay I (Poblacion)',
            'Barangay II (Poblacion)',
            'Barangay III (Poblacion)',
            'Barangay IV (Poblacion)',
            'Barangay V (Poblacion)',
            'Alipit',
            'Bagumbayan',
            'Bubukal',
            'Calios',
            'Duhat',
            'Gatid',
            'Jasaan',
            'Labuin',
            'Malinao',
            'Oogong',
            'Pagsawitan',
            'Palasan',
            'Poblacion I',
            'Poblacion II',
            'Poblacion III',
            'Poblacion IV',
            'Poblacion V',
            'San Jose',
            'San Juan',
            'San Pablo Norte',
            'San Pablo Sur',
            'Santo Angel Central',
            'Santo Angel Norte',
            'Santo Angel Sur',
        ];

        foreach ($barangays as $name) {
            Barangay::query()->firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $name],
                [
                    'municipality' => 'Santa Cruz',
                    'province' => 'Laguna',
                    'region' => 'IV-A CALABARZON',
                ]
            );
        }
    }
}
