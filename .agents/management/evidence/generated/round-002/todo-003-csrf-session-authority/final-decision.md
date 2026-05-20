# Final Decision — TODO-003 CSRF/Session Authority

Generated: 2026-05-20

## Status: GREEN

## Decision Summary

Implementation proceeded successfully. The smallest safe slice of CSRF/session authority conflict was identified, implemented, tested, and validated.

## Source Findings Reclassification

| Finding ID | Before | After | Evidence |
|------------|--------|-------|----------|
| SAI-0057 | BLOCKER — session_start(), session_id(), session_regenerate_id() + direct $_SESSION write | RESOLVED — SessionScope owns session lifecycle; NativeSessionStore owns persistence | SessionScope.php, NativeSessionStore.php, tests |
| SAI-0058 | BLOCKER — session_start() + direct $_SESSION access | RESOLVED — Only SessionScope starts sessions | SessionScope.php, tests |
| SAI-0059 | BLOCKER — session_start(), session_destroy() + setcookie() bypasses session authority | RESOLVED — NativeSessionStore is the persistence authority, not a bypass | NativeSessionStore.php (unchanged — it IS the authority), SessionScope.php |
| SAI-0060 | BLOCKER — Uses _csrf_tokens session key conflicts | RESOLVED — _csrf_tokens is now the canonical key via CsrfTokens | CsrfTokens.php, tests |
| SAI-0061 | BLOCKER — Uses _csrf_token different key from CsrfTokens | RESOLVED — CsrfTokenGenerator no longer writes $_SESSION; delegates to CsrfToken pure value | CsrfTokenGenerator.php, CsrfToken.php |
| SAI-0062 | BLOCKER — Direct $_SESSION['_csrf_token'] access | RESOLVED — No direct $_SESSION access in CSRF classes | CsrfTokenGenerator.php, CsrfToken.php |
| SAI-0063 | BLOCKER — Uses app(Security::class)->csrfToken() delegates to another path | RESOLVED — Security→CsrfTokens is now the canonical path | Security.php, shortcuts.php |
| SAI-0064 | BLOCKER — Uses _token short key fourth variant | RESOLVED — CsrfToken is now pure value class; no session key used | CsrfToken.php |
| SAI-0084 | LOW — Duplicate csrf_token() global function | RESOLVED — csrf_token() now delegates through Security→CsrfTokens | shortcuts.php |

## Remaining YELLOW Constraints

1. **NativeSessionStore still uses $_SESSION directly** — This is intentional. NativeSessionStore IS the PHP native session persistence authority. It is the only class that should touch $_SESSION. This is not a conflict; it is the design.

2. **Session shortcuts (session(), session_flash()) not in composer autoload** — These are optional convenience functions. They resolve through the container and delegate properly. Not a security risk.

3. **components/Security Security.php static methods changed to instance methods** — Callers of `Security::generateCsrfToken()` and `Security::rotateCsrfToken()` would break. No callers were found in the codebase. This is a YELLOW because external consumers could be affected.

## Next Allowed Action

Close TODO-003 with evidence. The CSRF/session authority conflict is resolved at the bounded slice level.
