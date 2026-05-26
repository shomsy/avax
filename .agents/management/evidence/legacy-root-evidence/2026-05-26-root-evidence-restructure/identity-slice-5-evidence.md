# Slice 5 Evidence: CredentialsGraph — MFA/Passkey DI Infrastructure

## Stage
Slice 5 — CredentialsGraph: MFA/Passkey boundaries, credential store

## Status
GREEN

## Why This Is GREEN

### validation
- `vendor/bin/phpunit --no-coverage` → 9499 tests, 27300 assertions, OK
- `vendor/bin/phpstan analyse [changed files] --memory-limit=1G` → clean, zero errors

### gates
- PHPStan: clean on all changed files
- PHPUnit: all 9499 tests pass
- Duplicate class test: passes (removed deprecated Assembly/CredentialsGraph)

### deviation_audit
- 0 BLOCKER, 0 HIGH, 0 MEDIUM findings

### corrections
1. **Created CredentialsGraph** (`components/Identity/Credentials/System/Configuration/Graphs/CredentialsGraph.php`)
   - Canonical DI registration for all Credentials component infrastructure
   - Registers: CredentialsConfiguration, CredentialStoreInterface, MfaStoreInterface, MfaChallengeStoreInterface, TotpInterface, LimitMfaAttempts, PasskeyCredentialStoreInterface, PasskeyChallengeStoreInterface
   - Boot method resets Credentials static facade for worker safety
   - Follows same pattern as AccessGraph

2. **Updated CredentialsServiceProvider** to delegate to CredentialsGraph::register/boot

3. **Updated RegisterAuthDefaults** to delegate to CredentialsGraph::register instead of duplicating MFA/Passkey store registrations
   - Added missing imports: InMemoryLifecycleStore, InMemoryScimDirectoryStore, InMemoryScimProvisionedIdentityStore, InMemorySessionStore, SessionStore, GenerateSessionId, RandomSessionId, InMemoryUserSource, NativeSessionStore, SessionCookieSettings
   - Removed duplicated MFA/Passkey store registrations (now owned by CredentialsGraph)

4. **Removed deprecated Assembly/CredentialsGraph** — old class only set store on static facade, replaced by proper DI registration in new CredentialsGraph

### remaining_deviations
None.

### suppression_check
No suppression detected. No phpstan baseline additions. No test skips.

### risk_assessment
CredentialsGraph now owns all Credentials infrastructure registration. RegisterAuthDefaults delegates to it, following the dogfooding pattern. Risk is contained to the files changed.

### severity_decision
GREEN — all validation clean, all gates clean, zero deviations, zero suppression.

## Files Changed

### New
- `components/Identity/Credentials/System/Configuration/Graphs/CredentialsGraph.php`

### Modified
- `components/Identity/Credentials/System/Configuration/CredentialsServiceProvider.php`
- `components/Identity/Auth/System/Configuration/Builders/RegisterAuthDefaults.php`

### Deleted
- `components/Identity/Credentials/System/Configuration/Assembly/CredentialsGraph.php` (deprecated, replaced)

## Next allowed action
Commit Slice 5, then proceed to Slice 6 (TokenGraph hardening)
