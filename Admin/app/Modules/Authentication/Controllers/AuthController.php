<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Modules\AuditLog\Contracts\AuditLogInterface;

class AuthController extends Controller
{
    protected $auditService;

    public function __construct(AuditLogInterface $auditService)
    {
        $this->auditService = $auditService;
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $this->auditService->logLogout($user);
        }
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('message', 'You have been logged out successfully.');
    }
}
