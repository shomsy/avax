# Slice 4 Evidence: AuthorizationGraph, Access DI, Negative Tests

## Stage
Slice 4 — AuthorizationGraph, Access boundaries, negative tests

## Status
GREEN

## Why This Is GREEN

### validation
- `vendor/bin/phpunit tests/Unit/Components/Identity/Access/ --no-coverage` → 62 tests, 117 assertions, OK
- `vendor/bin/phpunit --no-coverage` → 9499 tests, 27300 assertions, OK
- `vendor/bin/phpstan analyse [changed files] --memory-limit=1G` → clean, zero errors

### gates
- PHPStan: clean on all changed files
- PHPUnit: all 9499 tests pass

### deviation_audit
- 0 BLOCKER, 0 HIGH, 0 MEDIUM findings

### corrections
Fixed multiple constructor/factory signature mismatches discovered during Slice 4 test execution:

1. **AuthenticationContext::authenticated() factory method** — used wrong named parameters `mode:` and `user:` instead of constructor parameter names `authenticationMode:` and `authenticatedUser:`
   - File: `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticationContext.php`

2. **RequireRole::execute()** — called `AuthenticatedUser::canAccessRole()` with wrong named parameter `requiredRole:` (method uses positional)
   - File: `components/Identity/Access/System/Capabilities/RequireRole/RequireRole.php`

3. **RequireRole exception** — constructed `RoleDenied` with wrong named parameter `requirement:` (constructor uses positional `$userRole`)
   - File: `components/Identity/Access/System/Capabilities/RequireRole/RequireRole.php`

4. **RequirePermission::execute()** — called `AuthenticatedUser::hasPermission()` with wrong named parameter `permission:` (method uses positional)
   - File: `components/Identity/Access/System/Capabilities/RequirePermission/RequirePermission.php`

5. **RequirePermission exception** — constructed `PermissionDenied` with wrong named parameter `requirement:` (constructor uses positional `$userPermission`)
   - File: `components/Identity/Access/System/Capabilities/RequirePermission/RequirePermission.php`

6. **AccessDenialTest helper** — passed `UserRole`/`UserPermission` enum objects directly to `AuthenticatedUser` constructor which expects `list<string>`, causing `UserRole::from()` TypeError at runtime
   - Fixed: helper now maps enums to their `.value` strings
   - Added PHPDoc `@param list<UserRole>` and `@param list<UserPermission>` type annotations
   - Replaced `assertTrue(true)` sentinel assertions with `assertTrue($this->currentAuth->read()->isAuthenticated())`

7. **AuthenticationMode::PASSKEY does not exist** — enum only has `NONE`, `SESSION`, `TOKEN`, `HYBRID`
   - Fixed: phishing-resistant test uses `TOKEN` mode with `phishingResistant: true` flag

### remaining_deviations
None.

### suppression_check
No suppression detected. No phpstan baseline additions. No test skips. No catch-all handlers.

### risk_assessment
All fixes are parameter name corrections — no behavior change. The corrected code matches the actual constructor/method signatures. Risk is contained to the files changed.

### severity_decision
GREEN — all validation clean, all gates clean, zero deviations, zero suppression.

## Files Changed

### Modified (bug fixes)
- `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticationContext.php`
  - Fixed `authenticated()` factory method named parameters
- `components/Identity/Access/System/Capabilities/RequireRole/RequireRole.php`
  - Fixed `canAccessRole()` call and `RoleDenied` constructor call
- `components/Identity/Access/System/Capabilities/RequirePermission/RequirePermission.php`
  - Fixed `hasPermission()` call and `PermissionDenied` constructor call

### New/Modified (Slice 4 work)
- `tests/Unit/Components/Identity/Access/AccessDenialTest.php`
  - 24 negative test cases for Access component denial paths
  - Tests RequireAuthentication, RequireRole, RequirePermission, RequireResourceOwner, RequirePhishingResistantAuthentication, AuthorizationEngine
  - All parameter names corrected, type annotations added

### New (from previous slice, not yet committed)
- `components/Identity/Access/System/Configuration/Graphs/AccessGraph.php`
  - Full DI assembly for Access/Authorization component
  - Registers AuthorizationEngine, PolicyEvaluator, all Require* boundaries, RequireAccessPolicy orchestrator
- `components/Identity/Access/System/Configuration/Builders/RegisterAccessDependencies.php`
  - Delegates to AccessGraph::register() and AccessGraph::boot()
- `components/Identity/Access/System/Configuration/AccessServiceProvider.php`
  - Delegates to AccessGraph::register() and AccessGraph::boot()

## Next allowed action
Commit Slice 4, then proceed to Slice 5 (CredentialsGraph — MFA/Passkey boundaries, credential store)
