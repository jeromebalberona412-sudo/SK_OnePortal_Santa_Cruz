<?php

namespace App\Modules\Accounts\Controllers;

use App\Modules\Accounts\Models\Barangay;
use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Accounts\Requests\ExtendTermRequest;
use App\Modules\Accounts\Requests\StoreAccountRequest;
use App\Modules\Accounts\Requests\UpdateAccountRequest;
use App\Modules\Accounts\Services\AccountService;
use App\Modules\Shared\Controllers\Controller;
use App\Modules\Shared\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function __construct(private readonly AccountService $accountService)
    {
    }

    public function indexFederation(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()
            ->with(['barangay', 'officialProfile.latestTerm'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('role', User::ROLE_SK_FED)
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', (int) $request->input('barangay_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $accounts = $query->paginate(10)->withQueryString();

        $accountType = 'sk_federation';

        return view('accounts::manage_account', compact('accounts', 'accountType'));
    }

    public function indexOfficials(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::query()
            ->with(['barangay', 'officialProfile.latestTerm'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('role', User::ROLE_SK_OFFICIAL)
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', (int) $request->input('barangay_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $accounts = $query->paginate(10)->withQueryString();

        $accountType = 'sk_officials';

        return view('accounts::manage_account', compact('accounts', 'accountType'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('accounts::add_sk_fed');
    }

    public function store(StoreAccountRequest $request): Response|RedirectResponse
    {
        $this->authorize('create', User::class);

        $account = $this->accountService->createAccount($request->validated(), $request->user());

        if ($request->expectsJson()) {
            return response([
                'success' => true,
                'message' => 'Account created successfully.',
                'data' => ['id' => $account->id],
            ]);
        }

        return redirect()->route('accounts.federation.index')
            ->with('status', 'Account created successfully.');
    }

    public function storeLegacy(Request $request): Response
    {
        $this->authorize('create', User::class);

        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'middle_initial' => ['nullable', 'string', 'max:5'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id))],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'barangay' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string'],
            'term_start' => ['required', 'date'],
            'term_end' => ['required', 'date', 'after:term_start'],
            'status' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $positionMap = [
            'sk_chairman' => 'Chairman',
            'sk_councilor' => 'Councilor',
            'sk_kagawad' => 'Kagawad',
            'sk_treasurer' => 'Treasurer',
            'sk_secretary' => 'Secretary',
            'sk_auditor' => 'Auditor',
            'sk_pio' => 'PIO',
        ];

        $statusMap = [
            'active' => User::STATUS_ACTIVE,
            'inactive' => User::STATUS_INACTIVE,
            'pending_approval' => User::STATUS_PENDING_APPROVAL,
            'suspended' => User::STATUS_SUSPENDED,
        ];

        $barangay = Barangay::query()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('name', trim((string) $request->input('barangay')))
            ->first();

        if (! $barangay) {
            return response([
                'success' => false,
                'errors' => ['barangay' => ['Selected barangay is not valid.']],
            ], 422);
        }

        $payload = [
            'first_name' => trim((string) $request->input('first_name')),
            'last_name' => trim((string) $request->input('last_name')),
            'middle_initial' => $request->filled('middle_initial') ? trim((string) $request->input('middle_initial')) : null,
            'suffix' => $request->filled('suffix') ? trim((string) $request->input('suffix')) : null,
            'email' => (string) $request->input('email'),
            'password' => (string) $request->input('password'),
            'role' => User::ROLE_SK_FED,
            'status' => $statusMap[strtolower((string) $request->input('status'))] ?? User::STATUS_PENDING_APPROVAL,
            'barangay_id' => $barangay->id,
            'position' => $positionMap[(string) $request->input('position')] ?? 'Councilor',
            'term_start' => (string) $request->input('term_start'),
            'term_end' => (string) $request->input('term_end'),
            'term_status' => $statusMap[strtolower((string) $request->input('status'))] ?? 'ACTIVE',
        ];

        $account = $this->accountService->createAccount($payload, $request->user());

        return response([
            'success' => true,
            'message' => 'Account created successfully.',
            'data' => ['id' => $account->id],
        ]);
    }

    public function edit(User $user): View
    {
        $this->authorize('update', User::class);

        if ($user->tenant_id !== request()->user()->tenant_id) {
            abort(404);
        }

        $user->load(['officialProfile.latestTerm', 'barangay']);

        return view('accounts::edit_sk_fed', compact('user'));
    }

    public function update(UpdateAccountRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', User::class);

        if ($user->tenant_id !== $request->user()->tenant_id) {
            abort(404);
        }

        $this->accountService->updateAccount($user, $request->validated(), $request->user());

        return back()->with('status', 'Account updated successfully.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        $this->authorize('deactivate', User::class);

        if ($user->tenant_id !== $request->user()->tenant_id) {
            abort(404);
        }

        $this->accountService->deactivate($user, $request->user());

        return back()->with('status', 'Account deactivated successfully.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', User::class);

        if ($user->tenant_id !== $request->user()->tenant_id) {
            abort(404);
        }

        $payload = $request->validate([
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $newPassword = $payload['password'] ?? Str::random(16);
        $this->accountService->resetPassword($user, $newPassword, $request->user());

        return back()->with('status', 'Password reset successfully.');
    }

    public function extendTerm(ExtendTermRequest $request, OfficialProfile $officialProfile): RedirectResponse
    {
        $this->authorize('extendTerm', User::class);

        if ($officialProfile->tenant_id !== $request->user()->tenant_id) {
            abort(404);
        }

        $this->accountService->extendTerm($officialProfile, $request->validated(), $request->user());

        return back()->with('status', 'Term extended successfully.');
    }
}
