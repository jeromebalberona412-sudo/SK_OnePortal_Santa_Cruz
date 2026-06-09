<?php

namespace App\Modules\Announcement\Controllers;

use App\Models\Barangay;
use App\Modules\Announcement\Services\BarangayProfileService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AnnouncementPageController extends Controller
{
    public function __construct(private readonly BarangayProfileService $barangayProfileService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $brgy = Barangay::find($user->barangay_id);
        $slug = $brgy ? Str::slug($brgy->name) : 'san-jose';
        $name = $brgy?->name ?? 'Your Barangay';
        $color = '#f5c518';
        $barangayProfiles = $this->barangayProfileService->listForTenant((int) $user->tenant_id);

        return view('Announcement::announcement', compact(
            'slug',
            'name',
            'color',
            'user',
            'barangayProfiles',
        ));
    }
}
