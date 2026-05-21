# Slice: AccessRuntime Enforcement + DX Cleanup

## Summary

Implemented real enforcement in `AccessRuntime` security methods that were previously empty stubs (security blocker), applied fluent property naming for readability, and added comprehensive tests proving enforcement works.

## Files Changed

### Production Code

| File | Change |
|------|--------|
| `components/Identity/Access/System/Capabilities/AccessRuntime/AccessRuntime.php` | Rewritten with 8 fluent-named constructor params and real enforcement in `requireAuthentication()`, `requireRole()`, `requirePermission()` |
| `components/Identity/Access/System/Capabilities/RequireAccessPolicy/RequireAccessPolicy.php` | Fixed named parameter mismatches (`requiredRole` -> `userRole`, `permission` -> `userPermission`) |
| `components/Identity/Access/System/Capabilities/RequireRole/RequireRole.php` | Fixed named parameter mismatches (`requiredRole` -> `userRole`, `requirement` -> `userRole` for RoleDenied) |
| `components/Identity/Access/System/Capabilities/RequirePermission/RequirePermission.php` | Fixed named parameter mismatch (`permission` -> `userPermission`) |
| `components/Identity/Access/System/Configuration/AccessServiceProvider.php` | Updated AccessRuntime construction with new fluent property names |
| `components/Identity/System/Configuration/Builders/IdentityRuntime.php` | Updated AccessRuntime construction with new fluent property names |
| `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticationContext.php` | Fixed `authenticated()` factory using wrong named params (`mode` -> `authenticationMode`, `user` -> `authenticatedUser`) |

### Test Code

| File | Change |
|------|--------|
| `tests/Unit/Components/Identity/Access/AccessCharacterizationTest.php` | Added 7 new enforcement tests, refactored `makeAccess()` helper |
| `tests/Unit/Components/Identity/System/IdentityTargetDslCharacterizationTest.php` | Renamed stub-assertion tests to denial-assertion tests |

## Fluent Property Names

AccessRuntime constructor now reads like a fluent API unit:

```php
public function __construct(
    private RequireAuthentication  $authentication,
    private RequireRole            $roles,
    private RequirePermission      $permissions,
    private AuthorizationEngine    $authorization,
    private RequireResourceOwner   $ownership,
    private RequireAccessPolicy    $policies,
    private BeginAdminElevation    $elevation,
    private EndAdminElevation      $endElevation,
) {}
```

Methods delegate naturally:
- `$this->authentication->execute()`
- `$this->roles->execute(userRole: $userRole)`
- `$this->permissions->execute(userPermission: $userPermission)`
- `$this->authorization->check(...)`
- `$this->ownership->execute(ownerUserId: $ownerUserId)`
- `$this->policies->execute(accessPolicy: $accessPolicy)`
- `$this->elevation->execute()`

## Enforcement Tests Added

| Test | Proves |
|------|--------|
| `requireRoleDeniesWrongRole` | Authenticated user with `user` role is denied `ADMIN` role (throws `RoleDenied`) |
| `requireRolePassesForCorrectRole` | Authenticated user with `admin` role passes `ADMIN` requirement |
| `requirePermissionDeniesMissingPermission` | Authenticated user with only `read.post` is denied `delete.post` (throws `PermissionDenied`) |
| `requirePermissionPassesForExistingPermission` | Authenticated user with `read.post` passes `read.post` requirement |
| `requirePolicyPassesForAuthenticatedUser` | AccessPolicy with role requirement passes for user with matching role |
| `requireResourceOwnerPassesForOwner` | Resource owner (matching userId) passes ownership check |
| `requireResourceOwnerDeniesNonOwner` | Non-owner (different userId) is denied (throws `ResourceOwnerDenied`) |

## Named Parameter Fixes

Three flows had named parameter mismatches that would have caused runtime errors when using named arguments:

1. **RequireRole**: `canAccessRole(requiredRole:)` -> `canAccessRole(userRole:)`, `RoleDenied(requirement:)` -> `RoleDenied(userRole:)`
2. **RequirePermission**: `hasPermission(permission:)` -> `hasPermission(userPermission:)`
3. **RequireAccessPolicy**: `execute(requiredRole:)` -> `execute(userRole:)`, `execute(permission:)` -> `execute(userPermission:)`
4. **AuthenticationContext::authenticated()**: Constructor call used `mode:` and `user:` instead of `authenticationMode:` and `authenticatedUser:`

## Validation

- PHPUnit: 163 Identity tests pass (22 in AccessCharacterizationTest, 62 in Access)
- PHPStan on Access: pre-existing findings only (PolicyEvaluator type, AttributeCondition arrays, mixed variables) - no new findings introduced
- `git diff --check`: clean

## Slice 1 Verification (pre-existing commit 14293ef2d)

Slice 1 (consolidate duplicate permission exceptions and authorization class) was committed in a prior session. Verified:

- `RequirePermission\PermissionDenied.php` - DELETED, no references remain
- `Foundation\Exception\PermissionDeniedException.php` - DELETED, no references remain
- `Capabilities\Authorization\Authorization.php` - DELETED, no references remain
- Canonical `Foundation\Exception\PermissionDenied.php` - single source of truth
- grep confirms zero references to deleted class names

## AccessPolicy Duplication Assessment

Two AccessPolicy classes exist:

| Path | Type | Status |
|------|------|--------|
| `Access/System/Capabilities/AccessPolicy.php` | Interface (legacy callable) | KEPT - deprecated, zero references, safe to delete in future cleanup slice |
| `Access/System/Capabilities/Policy/AccessPolicy.php` | Value object (declarative policy) | CANONICAL - used by RequireAccessPolicy, AccessRuntime, Access, Authorization facade |

The legacy `Capabilities\AccessPolicy` interface has zero references in production or test code. It is marked `@deprecated` and may be safely deleted in a future cleanup slice. Not deleted here to keep this slice bounded.

## Additional Cleanup in This Slice

- Removed unused import of non-existent `Policy\Foundation\AccessPolicy` from `IdentityTargetDslCharacterizationTest.php`
- Removed unused `$engine` property from `AccessCharacterizationTest.php`

## Security Impact

**Before**: `requireAuthentication()`, `requireRole()`, `requirePermission()` were empty stubs that allowed any request through.

**After**: All three methods now delegate to their respective Require* flows which:
- Check for authenticated user (throw `Unauthenticated` for guests)
- Check role hierarchy (throw `RoleDenied` for insufficient role)
- Check permission membership (throw `PermissionDenied` for missing permission)

This closes a HIGH security finding where access control enforcement was non-functional.

## Remaining Risk

- Full validation suite not run (PHPStan, governance gates)
- `requirePolicy` and `requireResourceOwner` are not on `AccessInterface` (only on concrete `Access` class) - potential API compatibility concern if interface is used as contract
