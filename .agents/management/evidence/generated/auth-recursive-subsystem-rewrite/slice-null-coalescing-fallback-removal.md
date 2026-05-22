# Slice: Replace Null-Coalescing Fallbacks in AssembleAuthIdentityGraph

Date: 2026-05-22
Branch: architecture/auth-recursive-subsystem-rewrite
Status: GREEN

## What Changed

Removed 6 optional constructor parameters from `AssembleAuthIdentityGraph` that were never passed by any caller:

- `CurrentAuthentication|null $currentAuthentication = null`
- `ProjectAuthenticatedUser|null $projectAuthenticatedUser = null`
- `RequireFreshMfa|null $requireFreshMfa = null`
- `GenerateBackupCodes|null $generateBackupCodes = null`
- `VerifyBackupCode|null $verifyBackupCode = null`
- `StartMfaChallenge|null $startMfaChallenge = null`

Updated the corresponding `resolveOrBuild*()` methods to always construct (removed null-coalescing fallback pattern).

## Why

These parameters were **never passed by any caller** (AuthBuilder, tests, or any other code). The fallback `?? new X()` construction was always taken, making the optional parameters dead code that hid the true dependency ownership.

This addresses findings:
- **DR-0363** through **DR-0368**: Null-coalescing fallback instantiation in assembly class
- **IT-006**: Null-coalescing fallback `new` construction (from identity-topology-review.md)
- Reduces constructor from 43 to 37 parameters (addresses part of **DR-0601**)

## Behavior Proof

- **No behavioral change**: The same objects are constructed with the same dependencies
- **Constructor reduced**: 43 → 37 parameters (6 fewer optional params that were never used)
- **Explicit ownership**: Assembly class clearly owns construction of these shared primitives
- **Fail-closed preserved**: All required dependencies remain required (non-nullable constructor params)

## Test Proof

- `AuthBuilderReadyGraphCharacterizationTest`: 26 tests, 288 assertions — GREEN
- `--filter "Auth"`: 131 tests, 597 assertions — GREEN
- PHPStan on modified file: 0 errors

## Files Changed

| File | Delta | Notes |
|------|-------|-------|
| AssembleAuthIdentityGraph.php | -18 lines | Removed 6 optional params, simplified 6 methods |

## Validation

```
vendor/bin/phpunit --filter "AuthBuilderReadyGraphCharacterizationTest" — 26/26 GREEN
vendor/bin/phpunit --filter "Auth" — 131/131 GREEN
vendor/bin/phpstan analyse AssembleAuthIdentityGraph.php — 0 errors
```
