# SK Officials Authentication Implementation Plan

## Summary

Create `documentation/SK_Officials/SK_Official_Authentication_Implementation_Plan.md` and place this plan in it. Implement the mature `SK_Federations` authentication stack inside `SK_Officials`, but rename and scope ownership to `SK Official`: only `role = sk_official` accounts can authenticate, Admin remains the account creator/manager, and SK Officials owns runtime login, logout, password reset, verification, session, device, and route access enforcement.

## Key Changes

- Add `laravel/fortify` to `SK_Officials/composer.json`, add `config/fortify.php`, and register `Laravel\Fortify\FortifyServiceProvider`, `App\Providers\FortifyServiceProvider`, and a new `App\Modules\Authentication\Providers\AuthenticationServiceProvider`.
- Replace the hardcoded demo login in `SK_Officials/app/Http/Controllers/AuthController.php` and all `session('authenticated')` checks with Laravel `auth`, `verified`, `single.session`, `sk_official.access`, `trusted.device`, and `prevent.back` middleware.
- Port the SK Federation auth services/controllers/middleware/tests into `SK_Officials/app/Modules/Authentication`, but rename ownership from `sk_fed` to `sk_official` everywhere: config keys, env keys, table names, route names, session keys, log channels, notification names, and user-facing mail copy.
- Keep `SK_Officials` on `App\Models\User` rather than introducing `App\Modules\Shared\Models\User`; expand that model with Admin/SK Federation-compatible role, tenant, status, lockout, session, verification, and notification helpers.
- Use Admin as the source-of-truth for account ownership: SK Officials must authenticate existing Admin-created accounts where `role = sk_official`, `status = ACTIVE`, `tenant.code = santa_cruz`, and `barangay_id` is present.

## Implementation

- Create `config/sk_official_auth.php` from `SK_Federations/config/sk_fed_auth.php` with defaults `SK_OFFICIAL_TENANT_CODE=santa_cruz` and `SK_OFFICIAL_REQUIRED_ROLE=sk_official`.
- Add SK Official auth tables using idempotent migrations: `sk_official_login_attempts`, `sk_official_auth_audit_logs`, `sk_official_trusted_devices`, `sk_official_email_verified_devices`, and `sk_official_feature_flags`.
- Align the `users` schema with Admin/SK Federation auth needs using guarded migrations: `tenant_id`, `role`, `status`, `barangay_id`, `must_change_password`, lockout fields, login metadata, single-session fields, OTP fields, and soft deletes if missing.
- Add or align `tenants`, `barangays`, `official_profiles`, and `official_terms` only when missing, matching Admin's schema so SK Officials can read Admin-created official ownership data.
- Move the current `SK_Officials/app/modules/Authentication` views/assets into the new autoloadable `SK_Officials/app/Modules/Authentication` module, update Vite inputs, and disable the old lowercase auth route file to avoid duplicate auth routes.
- Implement Fortify login using a `SkOfficialAuthenticationService` adapted from SK Federation: validate credentials, block wrong role/tenant/status, require verified email, handle trusted device checks, handle single-session conflicts, log attempts, record login metadata, and return generic auth errors.
- Add SK Official routes: `/login`, `/logout`, `/forgot-password`, `/reset-password/{token}`, `/email/verify/*`, `/session/takeover/*`, `/heartbeat`, and route names under `sk_official.*`.
- Add password reset from SK Federation with SK Official scoping, Turnstile middleware, rate limiters, password complexity, password reuse prevention, token cleanup, remember token reset, and database session invalidation.
- Add first-login password ownership from Admin's `must_change_password`: after login, redirect those users to `/change-password`; block normal protected routes until the password is updated; clear `must_change_password` after a successful update.
- Convert existing SK Officials UI routes to one protected route model and remove manual session checks. The protected portal routes should require `auth`, `verified`, `single.session`, `sk_official.access`, `trusted.device`, `must.change.password`, and `prevent.back`, except the password-change route needed to clear `must_change_password`.

## Test Plan

- Add Pest feature tests matching SK Federation coverage but scoped to SK Officials: verified official login succeeds, wrong password fails, `sk_fed`/`admin`/plain `user` roles are blocked, inactive/suspended officials are blocked, wrong tenant is blocked, and missing barangay ownership is blocked.
- Test email verification: unverified official receives verification mail, wait-status logs in only after verification, and fresh device verification requires a newer `email_verified_at`.
- Test password reset: in-scope official receives reset notification, out-of-scope user does not, invalid/expired token fails, password reuse fails, successful reset deletes token and sessions, and new password can log in.
- Test single-session ownership: active-session conflict starts takeover OTP, valid OTP terminates old session, inactive previous session allows login, heartbeat updates ownership, and stolen session ownership logs out.
- Test first-login password change: `must_change_password=true` redirects to `/change-password`, protected pages are blocked until changed, successful change clears the flag and logs out or redirects according to the chosen UX.
- Run `php artisan test`, `php artisan route:list`, `composer dump-autoload`, and `npm run build` inside `SK_Officials`.

## Assumptions

- The requested scope is the full SK Federation authentication stack, not a minimal username/password login.
- Admin remains responsible for creating and managing SK Official accounts; SK Officials does not add public registration.
- `SK_Officials` uses the same database as Admin or receives the same `users`, `tenants`, `barangays`, and `official_profiles` data before rollout.
- All `sk_fed_*` names must become `sk_official_*` in the SK Officials implementation so the feature is owned by SK Official, not Federation.
