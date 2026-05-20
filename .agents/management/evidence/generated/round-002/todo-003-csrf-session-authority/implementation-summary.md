# Implementation Summary — TODO-003 CSRF/Session Authority

Generated: 2026-05-20

## Decision: Implementation Proceeded — YES

## Files Changed

| File | Change |
|------|--------|
| `components/HTTP/Session/System/PublicSurface/SessionScope.php` | Removed `$_SESSION = $this->data` from `sync()`. In-memory state stays in `$this->data`; persistence is exclusively via `SessionStoreInterface.save()`. Eliminates double-write conflict with `NativeSessionStore`. |
| `components/HTTP/Security/System/Capabilities/Csrf/CsrfToken.php` | Converted from session-mutating static class to pure value class. `generate()` returns a 64-char hex token without any `$_SESSION` I/O or `session_start()`. Removed `token()` and `rotate()` static methods. |
| `components/HTTP/Security/System/Capabilities/Csrf/CsrfTokenGenerator.php` | Removed all `$_SESSION` I/O. Now delegates token generation to `CsrfToken::generate()`. `validate()` now requires explicit `$expectedToken` parameter (no implicit session lookup). |
| `components/HTTP/Security/System/Capabilities/Csrf/CsrfVerifier.php` | Removed `$_SESSION['_token']` fallback. `verify()` now requires both `$token` and `$sessionToken` parameters. Returns false if either is null (fail-closed). |
| `components/HTTP/Security/System/PublicSurface/shortcuts.php` | `csrf_token()` now delegates through `Security PublicSurface → CsrfTokens` (single authority). Added `assert()` for type safety. Added `@param array<string, string>` for `secure_headers()`. |
| `components/Security/System/PublicSurface/Security.php` | Injected `CsrfTokens` dependency. Changed `generateCsrfToken()` and `rotateCsrfToken()` from static to instance methods delegating to `CsrfTokens`. `verifyCsrfToken()` uses `CsrfTokens.validateToken()` when no explicit session token provided, falls back to `CsrfVerifier` with explicit token. |
| `tests/Unit/Components/HTTP/Security/CsrfAuthorityTest.php` | New test file — 17 tests proving CSRF authority unification. |
| `tests/Unit/Components/HTTP/Security/HttpSecurityCapabilitiesTest.php` | Updated to use new `CsrfVerifier::verify()` signature (requires explicit session token). Removed unused `CsrfToken` import. |

## Security Design

### Before (BLOCKER):
- 4 different session keys for CSRF: `_token`, `_csrf_token`, `_csrf_tokens`
- 3 classes directly mutated `$_SESSION` outside Session authority
- 3 classes could call `session_start()` independently
- `SessionScope.sync()` and `NativeSessionStore.write()` both wrote `$_SESSION`
- Long-lived workers could have stale/conflicting session state

### After:
- Single CSRF authority: `CsrfTokens` (via Session PublicSurface)
- Zero direct `$_SESSION` mutations outside `NativeSessionStore`
- Zero `session_start()` calls outside `SessionScope`
- `SessionScope.sync()` is now a no-op for `$_SESSION` — persistence via store only
- `CsrfToken` is a pure value class — generates tokens without any I/O
- `CsrfVerifier` is a pure comparison — requires explicit tokens
- `CsrfTokenGenerator` delegates to `CsrfToken` — no session I/O
- All CSRF operations flow: `Security → CsrfTokens → Session → SessionScope → SessionStoreInterface`

### Long-Lived Worker Safety:
- No static mutable CSRF state
- No implicit `session_start()` from CSRF classes
- Session scope is bound to the Session instance lifecycle
- `$_SESSION` superglobal is only touched by `NativeSessionStore` during explicit persistence

## Tests Added: 17

| Test | Proves |
|------|--------|
| `test_valid_csrf_token_is_accepted` | Valid token passes |
| `test_invalid_csrf_token_is_rejected` | Invalid token fails (fail-closed) |
| `test_null_csrf_token_is_rejected` | Null token fails |
| `test_empty_csrf_token_is_rejected` | Empty string token fails |
| `test_token_is_consumed_after_validation` | One-time use token |
| `test_new_token_generated_after_consumption` | Rotation works |
| `test_multiple_tokens_can_exist_simultaneously` | Multi-token model |
| `test_csrf_token_generate_is_pure_value` | No `$_SESSION` mutation |
| `test_csrf_verifier_requires_explicit_session_token` | No `$_SESSION` fallback |
| `test_csrf_verifier_matches_correct_tokens` | Timing-safe comparison |
| `test_csrf_verifier_rejects_mismatched_tokens` | Mismatch detection |
| `test_csrf_verifier_rejects_null_submitted_token` | Null input fails |
| `test_csrf_token_generator_no_session_io` | Generator no `$_SESSION` I/O |
| `test_csrf_token_generator_validate_requires_expected` | Generator needs explicit token |
| `test_duplicate_csrf_tokens_instances_share_session_authority` | No conflicting authority |
| `test_csrf_token_generator_and_csrf_tokens_produce_compatible_tokens` | Cross-class compatibility |
| `test_session_destroy_invalidates_csrf_tokens` | Session lifecycle integration |
| `test_session_regenerate_preserves_csrf_ability` | Regeneration integration |

## Validation Summary

| Command | Result |
|---------|--------|
| `composer validate --no-check-publish` | PASS |
| `composer dump-autoload -o` | PASS |
| `phpunit --filter "Csrf\|Session" --no-coverage` | 17 tests, 51 assertions — OK |
| `phpstan analyse (changed files)` | CLEAN |
| `check-runtime-composition-leaks.php` | PASS |
| `check-governance-index-current.php` | GREEN |
| `check-root-evidence-hygiene.php` | GREEN |
