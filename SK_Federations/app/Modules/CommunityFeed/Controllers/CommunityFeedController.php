<?php

namespace App\Modules\CommunityFeed\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommunityFeedController extends Controller
{
    public function index(Request $request): View
    {
        return view('community_feed::index', ['user' => $request->user()]);
    }
}
