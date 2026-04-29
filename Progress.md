 SK OnePortal Santa Cruz - Progress Report

As of: April 21, 2026
Scope: Admin, SK Federation, SK Officials, Kabataan
Method: Source-code-first analysis with documentation cross-check

 1. Executive Snapshot

| Role | Current State | Security Posture | Database Maturity | Test Maturity |

| Admin | Core admin governance is active (auth, account lifecycle, audit) with modular architecture. | Strong baseline (Fortify, lockout, 2FA middleware, HTTPS and security headers). | Real tenant-scoped data model and transactional account operations. | Low to medium (mostly default tests, but robust implementation in services). |
| SK Federation | Authentication and session security are the most advanced part of the monorepo. | Strong and layered (tenant-role enforcement, lockout, trusted device, session takeover OTP, turnstile, reset throttles). | Mature auth data model; non-auth modules are still largely prototype. | High for auth flows (multiple feature tests). |
| SK Officials | Mostly UI-first prototype with session-flag checks and demo login credentials. | Weak for production (hardcoded credentials and inconsistent auth model). | Minimal direct DB usage in feature code. | Low (default example test only). |
| Kabataan | Prototype-first portal focused on public feed and session-based demo flows. | Basic prototype protections only; no production-grade auth implementation yet. | Minimal direct DB usage in feature code; mostly mock/session data. | Low (default example test only). |

 2. Role-by-Role Feature Matrices

Confidence tags reflect evidence strength, not product quality:
- `[High]`: directly confirmed in active code paths, schema, or tests.
- `[Medium]`: confirmed in code, but partially limited by placeholders, disabled writes, or cross-module inference.
- `[Low]`: mostly documentation-backed or shallow UI-shell observation with limited backend confirmation.

 Role: Admin

| Feature | Claims | Code + Security Strategy | DB Relations | Evidence |

| Authentication and Access Control | Fortify login with custom `authenticateUsing` is active `[High]`; 2FA challenge flow is active `[High]`; forgot/reset controller endpoints are still UI placeholders `[High]`; overall readiness is only partially production-ready `[Medium]`. | Controller -> `AuthenticationService` -> `LoginSecurityService` + `AuditLogService` `[High]`. Security stack includes HTTPS + security headers, lockout tracking, `ensure2fa`, role checks, and generic auth failure behavior `[High]`. | RW: `users`, `login_attempts`, `admin_activity_logs`, `sessions` `[High]`. Relations: `admin_activity_logs.user_id -> users.id`, `sessions.user_id -> users.id`, `login_attempts.email <-> users.email` `[High]`. No explicit multi-table transaction in auth flow `[High]`. | `Admin/bootstrap/app.php`<br>`Admin/config/fortify.php`<br>`Admin/app/Providers/FortifyServiceProvider.php`<br>`Admin/app/Modules/Authentication/Controllers/AuthController.php`<br>`Admin/app/Modules/Authentication/Services/{AuthenticationService,LoginSecurityService}.php`<br>`Admin/app/Modules/AuditLog/Services/AuditLogService.php` |
| Accounts Governance | Tenant-scoped create/update/deactivate/reset/extend-term flows are active `[High]`; federation and officials account search/filter/listing is implemented `[High]`; governance logic is production-leaning `[Medium]`. | `DB::transaction` is used for critical lifecycle flows `[High]`. Validation is split into FormRequests and authorization into Policy/Gate layers `[High]`. Security relies on admin-only + `ensure2fa`, tenant-boundary assertions, and self-action guards `[High]`. | RW: `users`, `tenants`, `barangays`, `official_profiles`, `official_terms`, `admin_activity_logs` `[High]`. Relations: `users.tenant_id -> tenants.id`, `users.barangay_id -> barangays.id`, `official_profiles.user_id -> users.id`, `official_profiles.tenant_id -> tenants.id`, `official_terms.official_profile_id -> official_profiles.id`, `admin_activity_logs.user_id -> users.id`, `admin_activity_logs.tenant_id -> tenants.id` `[High]`. | `Admin/app/Modules/Accounts/Routes/accounts.php`<br>`Admin/app/Modules/Accounts/Controllers/AdminAccountController.php`<br>`Admin/app/Modules/Accounts/Services/AccountService.php`<br>`Admin/app/Modules/Accounts/Models/{Barangay,OfficialProfile,OfficialTerm}.php`<br>`Admin/app/Modules/Accounts/Database/Migrations/2026_02_27_000005_create_tenants_table.php`<br>`Admin/app/Modules/Accounts/Database/Migrations/2026_02_27_000040_create_official_terms_table.php` |
| Audit Monitoring | Audit viewer/filtering is active `[High]`; admin services write local audit entries and can read SK Federation auth audit logs `[High]`; monitoring workflow is operational `[Medium]`. | Uses a dedicated `AuditLogInterface` abstraction plus a filterable controller `[High]`. Audit UI is protected by `auth` and `ensure2fa` middleware `[High]`. | Write: `admin_activity_logs` `[High]`. Read/filter: `sk_fed_auth_audit_logs` with related user loading `[High]`. Relations: `admin_activity_logs.user_id -> users.id`, `sk_fed_auth_audit_logs.user_id -> users.id`, `sk_fed_auth_audit_logs.tenant_id -> tenants.id` `[High]`. | `Admin/app/Modules/AuditLog/routes/web.php`<br>`Admin/app/Modules/AuditLog/Controllers/AuditLogController.php`<br>`Admin/app/Modules/AuditLog/Services/AuditLogService.php`<br>`Admin/app/Modules/AuditLog/Models/{AdminActivityLog,SkFedAuthAuditLog}.php` |
| Dashboard and Profile Pages | Views are active and user-context aware `[High]`; module-level business orchestration is minimal `[High]`; current state is a stable UI shell rather than a rich domain module `[Medium]`. | Thin controllers return views directly `[High]`. Access protection comes from existing auth and route layering `[Medium]`. | No direct controller-level DB interaction was confirmed `[High]`. | `Admin/app/Modules/Dashboard/Controllers/DashboardController.php`<br>`Admin/app/Modules/Profile/Controllers/ProfileController.php` |

 Role: SK Federation

| Feature | Claims | Code + Security Strategy | DB Relations | Evidence |

| Authentication, Device Trust, and Single-Session Control | Credential checks, lockout handling, tenant-role scope checks, email verification wait state, trusted device reverification, single-session conflict handling, takeover OTP, and heartbeat are all present `[High]`; this is the most mature auth/session area in the monorepo `[Medium]`. | Service orchestration spans tenant context, feature flags, lockout logic, device trust, suspicious login, and audit logging `[High]`. `lockForUpdate` is used during takeover OTP verification `[High]`. Security uses role/trusted-device/single-session/turnstile middleware, host-consistency protection, and config-driven limits `[High]`. | High-volume RW: `users`, `tenants`, `sk_fed_login_attempts`, `sk_fed_auth_audit_logs`, `sk_fed_trusted_devices`, `sk_fed_email_verified_devices`, `sk_fed_feature_flags`, `sessions` `[High]`. Relations: `users.tenant_id -> tenants.id`, `sk_fed_login_attempts.user_id -> users.id`, `sk_fed_auth_audit_logs.user_id -> users.id`, `sk_fed_auth_audit_logs.tenant_id -> tenants.id`, `sk_fed_trusted_devices.user_id -> users.id`, `sk_fed_email_verified_devices.user_id -> users.id`, `sessions.user_id -> users.id` `[High]`. | `SK_Federations/bootstrap/app.php`<br>`SK_Federations/config/sk_fed_auth.php`<br>`SK_Federations/app/Modules/Authentication/Routes/auth.php`<br>`SK_Federations/app/Modules/Authentication/Services/{AuthenticationService,LoginSecurityService,TrustedDeviceService,EmailVerificationDeviceService,FeatureFlagService,AuthAuditLogService}.php` |
| Password Reset Flow | Scoped reset request/completion flow is active `[High]`; token validity, complexity, password reuse checks, and session invalidation are implemented `[High]`; core reset flow maturity is high `[Medium]`. | Service-based flow uses the Laravel password broker plus explicit pre/post checks `[High]`. Defensive schema guards (`Schema::hasColumn`, `Schema::hasTable`) are present `[High]`. Security adds turnstile, dedicated throttles, tenant-role eligibility checks, session invalidation, and remember-token clearing `[High]`. | RW: `users`, `password_reset_tokens`, `sessions`, `sk_fed_auth_audit_logs`, tenant-scoped lookups via `tenants` `[High]`. Relations: `password_reset_tokens.email <-> users.email`, `sessions.user_id -> users.id`, `sk_fed_auth_audit_logs.user_id -> users.id` `[High]`. | `SK_Federations/app/Modules/Authentication/Controllers/AuthController.php`<br>`SK_Federations/app/Modules/Authentication/Services/PasswordResetService.php`<br>`SK_Federations/app/Modules/Authentication/Providers/AuthenticationServiceProvider.php`<br>`SK_Federations/app/Modules/Authentication/Middleware/VerifyTurnstile.php` |
| Profile Module | Profile read path is active `[High]`; profile update and password update are intentionally disabled `[High]`; module maturity is partial/read-only `[Medium]`. | Read-only controller path with schema-guarded queries `[High]`. Security stays inside authenticated request context, with write actions blocked in controller code `[High]`. | Read-only access to `users`, `official_profiles`, and `barangays` `[High]`. Relations: `official_profiles.user_id -> users.id`, `users.barangay_id -> barangays.id`, `official_profiles.tenant_id -> tenants.id` `[High]`. | `SK_Federations/app/Modules/Profile/Controllers/ProfileController.php`<br>`SK_Federations/app/Modules/Profile/Models/{OfficialProfile,Barangay}.php` |
| Community Feed | Main feed/profile pages exist `[High]`; create-post path is explicitly prototype and redirects with success `[High]`; overall module remains prototype UI `[High]`. | Controller returns views plus static barangay profile arrays `[High]`. Security depends on authenticated module routing rather than deeper domain enforcement `[Medium]`. | No direct DB interaction is present in the current controller path `[High]`. | `SK_Federations/app/Modules/CommunityFeed/Controllers/CommunityFeedController.php`<br>`documentation/SK_Fed/2026-03-29.md` |
| Barangay Monitoring | Monitoring pages are implemented with static computed arrays/catalog data `[High]`; analytics experience is still prototype UI `[High]`. | In-controller data composition drives dashboard/detail views `[High]`. Security is inherited from authenticated portal routes `[Medium]`. | No direct DB interaction is present in the reviewed controller path `[High]`. | `SK_Federations/app/Modules/BarangayMonitoring/Controllers/BarangayMonitoringController.php`<br>`documentation/SK_Fed/2026-03-29.md` |
| Kabataan Monitoring and Dashboard Shell | Kabataan monitoring is a view shell `[High]`; dashboard returns only a user-context view `[High]`; feature maturity is prototype/early shell `[Medium]`. | Thin controllers with little domain orchestration `[High]`. Security depends on the surrounding auth/session middleware stack `[Medium]`. | No direct DB interaction is present in current controller logic `[High]`. | `SK_Federations/app/Modules/KabataanMonitoring/Controllers/KabataanMonitoringController.php`<br>`SK_Federations/app/Modules/Dashboard/Controllers/DashboardController.php` |

 Role: SK Officials

| Feature | Claims | Code + Security Strategy | DB Relations | Evidence |

| Authentication | Login uses one hardcoded credential pair `[High]`; authenticated state is only a session flag `[High]`; no real user-model authentication flow is executed `[High]`; overall state is prototype/demo only `[High]`. | Simple controller/session implementation drives demo behavior `[High]`. Security is limited to guest/web grouping and back-history cache-control middleware; no real credential store or tenant/role checks were confirmed `[High]`. | No direct DB interaction is present in the active authentication controller `[High]`. | `SK_Officials/app/Http/Controllers/AuthController.php`<br>`SK_Officials/routes/web.php`<br>`SK_Officials/app/Http/Middleware/PreventBackHistory.php` |
| Core Modules | Dashboard, profile, calendar, announcements, committees, programs, budget, KK profiling requests, and ABYIP routes are mostly closure-based UI endpoints with session-flag checks `[High]`; dynamic module routes load from `app/modules/*/routes/*.php` `[High]`; overlapping top-level and module route layers exist `[High]`; current state is prototype navigation with auth-model inconsistency risk `[Medium]`. | Route design is UI-first and closure-heavy `[High]`. Security mostly relies on manual `session('authenticated')` checks, while ABYIP uses `auth`, creating an inconsistent guard model `[High]`. | No direct DB interaction was confirmed in reviewed route closures/controllers `[High]`. | `SK_Officials/app/Providers/AppServiceProvider.php`<br>`SK_Officials/routes/web.php`<br>`SK_Officials/app/modules/Authentication/routes/auth.php`<br>`SK_Officials/app/modules/{ABYIP,Committees,Programs,Calendar,Announcement,Kabataan}/routes/*.php` |

 Role: Kabataan

| Feature | Claims | Code + Security Strategy | DB Relations | Evidence |

| Authentication (Prototype) | Login creates a prototype session user and bypasses persistent auth `[High]`; registration is TODO `[High]`; email verification and resend/status are simulated `[High]`; forgot-password only validates email existence and reset returns a success message `[High]`; overall maturity is prototype only `[High]`. | Prototype-mode branching keeps production code paths commented out `[High]`. Session keys carry authenticated state and user payload `[High]`. Security uses guest route groups and session invalidation on logout, but lacks persistent identity verification and a real token lifecycle `[High]`. | Read-only lookup against `users` for `exists:users,email` in forgot-password `[High]`. No relation graph beyond that single-table validation was confirmed `[High]`. | `Kabataan/app/Modules/Authentication/Routes/auth.php`<br>`Kabataan/app/Modules/Authentication/Controllers/AuthController.php` |
| Public Homepage and About | Public feed and about pages are active `[High]`; content is static and interactions are modal-gated `[High]`; current state is prototype/public UX `[Medium]`. | Controller-driven static feed/category payloads with frontend filtering/modal behavior `[High]`. Security is public by design, with actions gated in UX rather than enforced as server-side domain operations `[High]`. | No direct DB interaction is present in homepage/about controller logic `[High]`. | `Kabataan/app/Modules/Homepage/Controllers/HomepageController.php`<br>`Kabataan/app/Modules/Homepage/Routes/web.php`<br>`documentation/Kabataan/2026-03-28.md` |
| Dashboard and Barangay Profile | Dashboard and barangay pages require a prototype session flag `[High]`; data is static/mock `[High]`; overall maturity is prototype only `[High]`. | Controllers compose static arrays and session payload mappings `[High]`. Security uses manual session checks at controller entry, without hardened auth middleware in route definition `[High]`. | No direct DB interaction is present in current controller logic `[High]`. | `Kabataan/app/Modules/Dashboard/Controllers/DashboardController.php`<br>`Kabataan/app/Modules/Dashboard/Routes/web.php` |
| Profile and Settings | Profile/settings views are driven by session data and sample participation datasets `[High]`; access control still depends on manual session checks `[High]`; module remains prototype only `[High]`. | Session-first profile composition uses fallback defaults plus static program lists `[High]`. Security remains manual rather than middleware-hardened `[High]`. | No direct DB interaction is present in current profile controller logic `[High]`. | `Kabataan/app/Modules/Profile/Controllers/ProfileController.php`<br>`Kabataan/app/Modules/Profile/Routes/web.php` |

 3. Cross-Role Coding Strategies

- Modular architecture is strongest in Admin and SK Federation, with provider-based route/view/migration loading.
- Service-layer orchestration is heavily used in Admin accounts and SK Federation auth.
- Controller thickness differs by portal:
- Thin and service-driven in Admin/SK Federation core auth.
- Thick/static-data controllers in Kabataan and SK Federation prototype modules.
- Frontend/backend boundary policy is explicit in Admin (`scripts/check-frontend-backend-boundary.sh`).

Evidence:
- Admin/scripts/check-frontend-backend-boundary.sh
- Admin/bootstrap/providers.php
- SK_Federations/bootstrap/providers.php
- SK_Officials/app/Providers/AppServiceProvider.php

 4. Cross-Role Security Strategies

- Admin and SK Federation implement layered controls (middleware + rate limiting + role/tenant scoping + audit).
- SK Federation adds strongest session/device controls (trusted devices, heartbeat, takeover OTP, turnstile).
- Kabataan and SK Officials are predominantly prototype/session-flag security models.

Evidence:
- Admin/bootstrap/app.php
- Admin/config/fortify.php
- SK_Federations/bootstrap/app.php
- SK_Federations/config/sk_fed_auth.php
- Kabataan/app/Modules/Authentication/Controllers/AuthController.php
- SK_Officials/app/Http/Controllers/AuthController.php

 5. Test and Quality Maturity

 Test coverage status

- SK Federation:
- Substantive auth test suite exists and exercises login gating, email verification behavior, trusted devices, reset flow, turnstile, throttling, takeover OTP, session heartbeat, and concurrency handling.
- Admin:
- Default example test only.
- Kabataan:
- Default example test only.
- SK Officials:
- Default example test only.

Evidence:
- SK_Federations/tests/Feature/Authentication/SkFedAuthenticationTest.php
- SK_Federations/tests/Feature/Authentication/SkFedPasswordResetTest.php
- SK_Federations/tests/Feature/Authentication/SingleSessionTakeoverTest.php
- Admin/tests/Feature/ExampleTest.php
- Kabataan/tests/Feature/ExampleTest.php
- SK_Officials/tests/Feature/ExampleTest.php

 Current diagnostics and implementation gaps observed

- Kabataan profile controller shows static-analysis issue around `withHeaders` call chain inference.
- SK Federation test file shows static-analysis issue around `$this->withSession` in Pest context.
- Admin/Kabataan/SK Officials example tests show static-analysis issue around `$this->get` in closure context.
- Admin auth controller currently contains a stray standalone `+` line in login flow.
- Kabataan provider list references `App\Modules\Chatbot\Providers\ChatbotServiceProvider`, but corresponding provider file is not present.

Evidence:
- Kabataan/app/Modules/Profile/Controllers/ProfileController.php
- SK_Federations/tests/Feature/Authentication/SkFedAuthenticationTest.php
- Admin/tests/Feature/ExampleTest.php
- Kabataan/tests/Feature/ExampleTest.php
- SK_Officials/tests/Feature/ExampleTest.php
- Admin/app/Modules/Authentication/Controllers/AuthController.php
- Kabataan/bootstrap/providers.php

 6. Database Coverage Checklist (Schema Cross-Check)

The following DB-interacting feature tables were confirmed in the consolidated schema dump and migration sets:

- users
- tenants
- barangays
- official_profiles
- official_terms
- login_attempts
- admin_activity_logs
- password_reset_tokens
- sessions
- sk_fed_login_attempts
- sk_fed_auth_audit_logs
- sk_fed_trusted_devices
- sk_fed_email_verified_devices
- sk_fed_feature_flags

Evidence:
- database_structure/SK_Oneportal.sql
- Admin/database/migrations
- Admin/app/Modules/Accounts/Database/Migrations
- Admin/app/Modules/Authentication/Database/Migrations
- Admin/app/Modules/AuditLog/Database/Migrations
- SK_Federations/database/migrations
- SK_Federations/app/Modules/Authentication/Database/Migrations

 7. Priority Risks and Recommended Next Steps

 Quick wins (1-2 days)

1. Finish the Admin password reset path or explicitly disable its exposed route surface until the backend implementation is real.
2. Resolve obvious hygiene issues: remove the stray `+` in the Admin auth controller and either add or remove the missing Kabataan Chatbot provider reference.
3. Align SK Officials route protection to one guard model in the current prototype so session-flag and `auth`-middleware behavior stop diverging.
4. Add a first-pass test layer outside SK Federation auth, starting with Admin account lifecycle flows and core route authorization checks.

 Structural work (1-2 sprints)

1. Replace prototype authentication in Kabataan and SK Officials with real identity flows, then retire hardcoded/session-only login logic.
2. Expand multi-portal regression coverage so Admin, Kabataan, and SK Officials have meaningful feature tests instead of example-only baselines.
3. Convert prototype data providers in SK Federation community/monitoring and Kabataan dashboard/profile modules into database-backed repositories or services once backend scope is approved.
