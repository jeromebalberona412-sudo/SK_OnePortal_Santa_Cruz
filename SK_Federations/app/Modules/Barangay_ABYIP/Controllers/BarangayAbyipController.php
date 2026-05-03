<?php

namespace App\Modules\Barangay_ABYIP\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class BarangayAbyipController
{
    public function index(Request $request): View
    {
        $abyipSubmissions = $this->getAbyipSubmissions();
        $user = auth()->user();
        return view('barangay_abyip::barangay_abyip', [
            'abyipSubmissions' => $abyipSubmissions,
            'user' => $user
        ]);
    }

    private function getAbyipSubmissions(): array
    {
        return [
            [
                'id' => 1,
                'barangay' => 'Poblacion I',
                'date_submitted' => 'Apr 30, 2026',
                'submitted_by' => 'Juan Dela Cruz',
                'title' => 'ABYIP CY 2026',
                'submitted_time' => '12:00 AM',
                'pdf_file' => 'abyip_poblacion1_2026.pdf'
            ],
            [
                'id' => 2,
                'barangay' => 'Poblacion II',
                'date_submitted' => 'Apr 29, 2026',
                'submitted_by' => 'Maria Santos',
                'title' => 'ABYIP CY 2026',
                'submitted_time' => '11:30 PM',
                'pdf_file' => 'abyip_poblacion2_2026.pdf'
            ],
            [
                'id' => 3,
                'barangay' => 'Poblacion III',
                'date_submitted' => 'Apr 28, 2026',
                'submitted_by' => 'Pedro Reyes',
                'title' => 'ABYIP CY 2026',
                'submitted_time' => '10:45 PM',
                'pdf_file' => 'abyip_poblacion3_2026.pdf'
            ],
            [
                'id' => 4,
                'barangay' => 'Poblacion IV',
                'date_submitted' => 'Apr 27, 2026',
                'submitted_by' => 'Ana Garcia',
                'title' => 'ABYIP CY 2026',
                'submitted_time' => '09:15 PM',
                'pdf_file' => 'abyip_poblacion4_2026.pdf'
            ],
            [
                'id' => 5,
                'barangay' => 'Labuin',
                'date_submitted' => 'Apr 26, 2026',
                'submitted_by' => 'Carlos Mendez',
                'title' => 'ABYIP CY 2026',
                'submitted_time' => '08:30 PM',
                'pdf_file' => 'abyip_labuin_2026.pdf'
            ],
            [
                'id' => 6,
                'barangay' => 'Pagsawitan',
                'date_submitted' => 'Apr 25, 2026',
                'submitted_by' => 'Rosa Dela Cruz',
                'title' => 'ABYIP CY 2026',
                'submitted_time' => '07:00 PM',
                'pdf_file' => 'abyip_pagsawitan_2026.pdf'
            ],
            [
                'id' => 7,
                'barangay' => 'Santisima Cruz',
                'date_submitted' => 'Apr 24, 2026',
                'submitted_by' => 'Jose Ramos',
                'title' => 'ABYIP CY 2026',
                'submitted_time' => '06:45 PM',
                'pdf_file' => 'abyip_santisima_2026.pdf'
            ],
            [
                'id' => 8,
                'barangay' => 'Bagumbayan',
                'date_submitted' => 'Apr 23, 2026',
                'submitted_by' => 'Linda Torres',
                'title' => 'ABYIP CY 2026',
                'submitted_time' => '05:30 PM',
                'pdf_file' => 'abyip_bagumbayan_2026.pdf'
            ],
            [
                'id' => 9,
                'barangay' => 'Alipit',
                'date_submitted' => 'Apr 22, 2026',
                'submitted_by' => 'Miguel Cruz',
                'title' => 'ABYIP CY 2026',
                'submitted_time' => '04:15 PM',
                'pdf_file' => 'abyip_alipit_2026.pdf'
            ],
            [
                'id' => 10,
                'barangay' => 'Bubukal',
                'date_submitted' => 'Apr 21, 2026',
                'submitted_by' => 'Elena Fernandez',
                'title' => 'ABYIP CY 2026',
                'submitted_time' => '03:00 PM',
                'pdf_file' => 'abyip_bubukal_2026.pdf'
            ],
        ];
    }
}
