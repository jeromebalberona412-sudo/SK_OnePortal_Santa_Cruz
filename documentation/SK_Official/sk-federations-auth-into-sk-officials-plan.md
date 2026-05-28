# SK Federations Auth into SK Officials - Implementation Plan

## Goal
Implement the SK_Federations authentication flow inside SK_Officials, with ownership and enforcement tied to SK_Official accounts (role, tenant scope, and session ownership).

## References (current behavior)
- SK_Federations auth flow: services, routes, and email verification + session takeover.
- SK_Officials auth flow: similar services plus official-specific status checks.
- Admin auth flow: Fortify integration, audit logging, and 2FA patterns for secure access controls.

## Scope and ownership rules
- Auth ownership must be enforced by SK_Officials: role `sk_official`, tenant scope, and active status.
- Session and verification keys must be SK_Officials-specific (no `sk_fed_*` session keys).
- Views, routes, and assets must live under SK_Officials modules and config.

## Plan
### 1) Inventory and parity check
- Map SK_Federations auth components to SK_Officials equivalents:
  - Services: AuthenticationService, LoginSecurityService, TenantContextService, TrustedDeviceService, EmailVerificationDeviceService, SuspiciousLoginService, AuthAuditLogService.
  - Controllers: AuthController and any OTP/session takeover helpers.
  - Routes: auth routes, verification wait + status, takeover wait, heartbeat, logout fallback.
  - Middleware: access control, trusted device, single session, rate limit, turnstile.
- Identify gaps in SK_Officials (missing services, routes, or views) vs SK_Federations.

### 2) Align ownership and config
- Ensure SK_Officials uses `config/sk_official_auth.php` everywhere:
  - Required role, tenant code, rate limits, verification wait, single-session settings.
- Replace any SK_Federations-specific session keys or route names with `sk_official_*` keys.
- Verify that the SK_Officials `User` model exposes `ROLE_SK_OFFICIAL` and `isActiveOfficial()` checks.

### 3) Port SK_Federations auth behaviors
- Bring over SK_Federations behaviors not already in SK_Officials:
  - Email verification wait + polling flow.
  - Session takeover OTP flow.
  - Trusted device registration + re-verification logic.
  - Suspicious login detection and alert notifications.
- Keep the login validation and lockout logic consistent with SK_Officials rules.

### 4) Align Fortify and middleware wiring
- In SK_Officials Fortify provider:
  - Confirm `Fortify::authenticateUsing` uses the updated SK_Officials AuthenticationService.
  - Ensure logout clears session ownership (parity with SK_Federations).
- In SK_Officials `bootstrap/app.php`:
  - Confirm middleware aliases used by auth routes (trusted device, single session, access).

### 5) Views and asset wiring
- Verify SK_Officials authentication views mirror SK_Federations coverage:
  - Login, verify notice, verify wait, takeover wait, password reset screens.
- If SK_Federations serves module assets via a route, ensure SK_Officials has an equivalent or uses Vite-only assets consistently.

### 6) Admin reference alignment
- Compare Admin login security patterns and audit logging:
  - Ensure SK_Officials audit logs capture login success/fail, lockouts, and verification completion.
  - Consider Admin 2FA flow as an optional enhancement path for SK_Officials.

### 7) Testing and validation
- Manual checks:
  - SK_Official login success, email verification wait flow, takeover OTP flow, trusted device registration, single-session heartbeat.
  - Ensure role and tenant mismatches are blocked with proper audit log entries.
- Automated checks (Pest):
  - Login success/fail scenarios, verification wait status, takeover OTP edge cases.

## Deliverables
- Updated SK_Officials auth services, routes, middleware, and views.
- Config validation for `sk_official_auth` settings.
- Auth flow parity with SK_Federations but enforced for SK_Official ownership.

## Risks and notes
- Session key collisions between SK_Federations and SK_Officials if naming is not fully separated.
- Missing view or asset parity could break verification or takeover screens.
- Ensure any Admin patterns reused do not introduce admin-only constraints.
