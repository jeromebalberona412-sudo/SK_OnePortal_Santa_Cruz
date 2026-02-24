<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\Shared\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Modules\AuditLog\Contracts\AuditLogInterface;

class TwoFactorAuthController extends Controller
{
    protected $auditService;

    public function __construct(AuditLogInterface $auditService)
    {
        $this->auditService = $auditService;
    }

    public function show(Request $request)
    {
        $user = $request->user();
        if ($user->two_factor_confirmed_at) {
            return redirect('/dashboard');
        }
        $google2fa = new Google2FA();
        if ($request->session()->has('two_factor_secret_temp')) {
            $secret = $request->session()->get('two_factor_secret_temp');
        } else {
            $secret = $google2fa->generateSecretKey();
            $request->session()->put('two_factor_secret_temp', $secret);
        }
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'SK OnePortal Admin'), $user->email, $secret
        );
        $renderer = new ImageRenderer(new RendererStyle(200), new SvgImageBackEnd());
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);
        return view('authentication::two-factor-setup', [
            'QRCode' => $qrCodeSvg, 'secretKey' => $secret,
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = $request->user();
        $secret = $request->session()->get('two_factor_secret_temp');
        if (!$secret) {
            return back()->withErrors(['code' => 'Session expired. Please try again.']);
        }
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($secret, $request->code);
        if (!$valid) {
            return back()->withErrors(['code' => 'Invalid code. Please try again.']);
        }
        $user->two_factor_secret = Crypt::encrypt($secret);
        $user->two_factor_confirmed_at = now();
        $recoveryCodes = $this->generateRecoveryCodes();
        $user->two_factor_recovery_codes = Crypt::encrypt(json_encode($recoveryCodes));
        $user->save();
        $request->session()->forget('two_factor_secret_temp');
        $this->auditService->log2FAEnabled($user);
        return view('authentication::two-factor-recovery-codes', ['recoveryCodes' => $recoveryCodes]);
    }

    public function showRecoveryCodes(Request $request)
    {
        $user = $request->user();
        if (!$user->two_factor_recovery_codes) {
            return redirect('/dashboard')->with('error', '2FA is not enabled.');
        }
        $recoveryCodes = json_decode(Crypt::decrypt($user->two_factor_recovery_codes), true);
        return view('authentication::two-factor-recovery-codes', ['recoveryCodes' => $recoveryCodes]);
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();
        if (!$user->two_factor_confirmed_at) {
            return redirect()->route('two-factor.setup')->with('error', '2FA must be enabled first.');
        }
        $recoveryCodes = $this->generateRecoveryCodes();
        $user->two_factor_recovery_codes = Crypt::encrypt(json_encode($recoveryCodes));
        $user->save();
        $this->auditService->log('recovery_codes_regenerated', $user);
        return view('authentication::two-factor-recovery-codes', [
            'recoveryCodes' => $recoveryCodes, 'regenerated' => true,
        ]);
    }

    protected function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        }
        return $codes;
    }
}
