#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

printf '%s\n' "Checking JS/PHP boundary rules..."

violations=0

# Rule 1: Backend PHP files must not contain frontend browser runtime APIs.
backend_js_hits="$(rg -n --glob '*.php' --glob '!**/Views/**' --glob '!resources/views/**' --glob '!storage/**' --glob '!vendor/**' "<script\\b|\\bdocument\\.|\\bwindow\\.|\\blocalStorage\\.|\\bsessionStorage\\.|\\bquerySelector\\s*\\(|\\baddEventListener\\s*\\(|\\bfetch\\s*\\(|\\baxios\\s*\\(" app routes config bootstrap database || true)"
if [[ -n "$backend_js_hits" ]]; then
    printf '%s\n' ""
    printf '%s\n' "Rule violation: frontend/browser JS APIs detected in backend PHP files:"
    printf '%s\n' "$backend_js_hits"
    violations=1
fi

# Rule 2: Frontend JS files must not make authorization decisions.
frontend_auth_hits="$(rg -n --glob '*.js' "if\\s*\\([^)]*\\b(isAdmin|permission|authorize|can\\(|cannot\\(|tenant_id|role\\s*[=!]=?)\\b" app/Modules/**/assets/js resources/js || true)"
if [[ -n "$frontend_auth_hits" ]]; then
    printf '%s\n' ""
    printf '%s\n' "Rule violation: potential authorization/business-rule conditionals found in frontend JS:"
    printf '%s\n' "$frontend_auth_hits"
    violations=1
fi

if [[ "$violations" -ne 0 ]]; then
    printf '%s\n' ""
    printf '%s\n' "Boundary check failed."
    printf '%s\n' "Policy: JS is for UI behavior only; backend auth/business validation must stay in PHP."
    exit 1
fi

printf '%s\n' "Boundary check passed."
