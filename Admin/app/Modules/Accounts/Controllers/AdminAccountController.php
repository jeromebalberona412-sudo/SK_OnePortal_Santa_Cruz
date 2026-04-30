<?php

namespace App\Modules\Accounts\Controllers;

use App\Modules\Accounts\Database\Seeders\BarangaySeeder;
use App\Modules\Accounts\Models\Barangay;
use App\Modules\Accounts\Models\OfficialProfile;
use App\Modules\Accounts\Requests\ExtendTermRequest;
use App\Modules\Accounts\Requests\StoreAccountRequest;
use App\Modules\Accounts\Requests\UpdateAccountRequest;
use App\Modules\Accounts\Services\AccountService;
use App\Modules\Shared\Controllers\Controller;
use App\Modules\Shared\Models\Tenant;
use App\Modules\Shared\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class AdminAccountController extends Controller
{
    public function __construct(private readonly AccountService $accountService)
    {
    }

    public function indexFederation(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $tenantId = $this->resolveTenantId($request->user());
        $this->ensureTenantBarangays($tenantId);

        $query = User::query()
            ->with(['barangay', 'officialProfile.latestTerm'])
            ->where('tenant_id', $tenantId)
            ->where('role', User::ROLE_SK_FED)
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('officialProfile', function ($profileQuery) use ($search) {
                        $profileQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('suffix', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', (int) $request->input('barangay_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $accounts = $query->paginate(10)->withQueryString();
        $barangays = Barangay::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        $accountType = 'sk_federation';

        return view('accounts::manage_account', compact('accounts', 'accountType', 'barangays'));
    }

    public function indexOfficials(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $tenantId = $this->resolveTenantId($request->user());
        $this->ensureTenantBarangays($tenantId);

        $query = User::query()
            ->with(['barangay', 'officialProfile.latestTerm'])
            ->where('tenant_id', $tenantId)
            ->where('role', User::ROLE_SK_OFFICIAL)
            ->orderByDesc('created_at');

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('officialProfile', function ($profileQuery) use ($search) {
                        $profileQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('suffix', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('barangay_id')) {
            $query->where('barangay_id', (int) $request->input('barangay_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $accounts = $query->paginate(10)->withQueryString();
        $barangays = Barangay::query()
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        $accountType = 'sk_officials';

        return view('accounts::manage_account', compact('accounts', 'accountType', 'barangays'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('accounts::add_sk_fed');
    }

    public function store(StoreAccountRequest $request): Response|RedirectResponse
    {
        $this->authorize('create', User::class);

        Log::info('AdminAccountController@store: Request received.');
        try {
            $validatedData = $request->validated();
            Log::info('AdminAccountController@store: Validation passed.', $validatedData);

            $tenantId = $this->resolveTenantId($request->user());
            $this->ensureTenantBarangays($tenantId);

            $user = $this->accountService->createAccount(
                $validatedData,
                $request->user()
            );
            Log::info('AdminAccountController@store: Account created successfully.', ['user_id' => $user->id]);

            if ($request->expectsJson()) {
                return response([
                    'success' => true,
                    'message' => 'Account created successfully.',
                    'data' => ['id' => $user->id],
                ]);
            }

            $redirectRoute = $user->role === User::ROLE_SK_OFFICIAL
                ? 'accounts.officials.index'
                : 'accounts.federation.index';

            return redirect()->route($redirectRoute)
                ->with('status', 'Account created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('AdminAccountController@store: Validation failed.', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('AdminAccountController@store: Error creating account.', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
            ]);

            return back()->with('error', 'Failed to create account. Please try again.');
        }
    }

    public function update(UpdateAccountRequest $request, User $user): RedirectResponse|JsonResponse
    {
        $this->resolveTenantId($request->user());

        $this->authorize('update', $user);

        $this->accountService->updateAccount($user, $request->validated(), $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account updated successfully.',
            ]);
        }

        return back()->with('status', 'Account updated successfully.');
    }

    public function deactivate(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $this->resolveTenantId($request->user());

        $this->authorize('deactivate', $user);

        $this->accountService->deactivate($user, $request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account deactivated successfully.',
            ]);
        }

        return back()->with('status', 'Account deactivated successfully.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->resolveTenantId($request->user());

        $this->authorize('resetPassword', $user);

        $payload = $request->validate([
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $newPassword = $payload['password'] ?? Str::random(16);
        $this->accountService->resetPassword($user, $newPassword, $request->user());

        return back()->with('status', 'Password reset successfully.');
    }

    public function extendTerm(ExtendTermRequest $request, OfficialProfile $officialProfile): RedirectResponse
    {
        $this->resolveTenantId($request->user());

        $this->authorize('extendTerm', $officialProfile);

        $this->accountService->extendTerm($officialProfile, $request->validated(), $request->user());

        return back()->with('status', 'Term extended successfully.');
    }

    private function resolveTenantId(User $admin): int
    {
        if ($admin->tenant_id !== null) {
            return $admin->tenant_id;
        }

        $tenant = Tenant::query()->firstOrCreate(
            ['code' => 'santa_cruz'],
            [
                'name' => 'Santa Cruz Federation',
                'municipality' => 'Santa Cruz',
                'province' => 'Laguna',
                'region' => 'IV-A CALABARZON',
                'is_active' => true,
            ]
        );

        $admin->forceFill(['tenant_id' => $tenant->id])->save();

        return $tenant->id;
    }

    private function ensureTenantBarangays(int $tenantId): void
    {
        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            return;
        }

        if (Barangay::query()->where('tenant_id', $tenantId)->exists()) {
            return;
        }

        BarangaySeeder::seedTenant($tenant);
    }
}
