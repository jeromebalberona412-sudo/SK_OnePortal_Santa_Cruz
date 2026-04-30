<?php

namespace App\Modules\Kabataan_Portal\Controllers;

use App\Http\Controllers\Controller;

class KabataanPortalController extends Controller
{
    public function index()
    {
        return view('kabataan_portal::portal');
    }
}
