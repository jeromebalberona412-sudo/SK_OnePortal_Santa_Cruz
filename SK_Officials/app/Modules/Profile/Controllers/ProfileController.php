<?php

namespace App\Modules\Profile\Controllers;

use App\Modules\Profile\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(private readonly ProfileService $profileService)
    {
    }

    public function index(Request $request): View
    {
        $profile = $this->profileService->getDisplayData($request->user());

        return view('Profile::profile', [
            'user' => $request->user(),
            'profile' => $profile,
        ]);
    }
}
