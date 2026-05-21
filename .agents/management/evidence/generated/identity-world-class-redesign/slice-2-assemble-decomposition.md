# Slice 2: AssembleAuthIdentityGraph Decomposition

Status: COMPLETED
Date: 2026-05-21
Branch: architecture/identity-world-class-redesign

## Purpose

Decompose the 678-line `AssembleAuthIdentityGraph::assemble()` megamethod into focused private methods. No behavior change — pure structural extraction.

## Approach

The original `assemble()` method inlined all object graph construction across 9+ domains in a single 678-line method. Each domain section was extracted to a private method that accepts only the objects it needs. The `assemble()` method became a thin coordinator (~50 lines) that calls domain methods and assembles the return array.

## Extracted Methods (18 total)

| Method | Lines | Responsibility |
|--------|-------|----------------|
| `resolveOrBuildCurrentAuthentication()` | 3 | Build or use pre-built CurrentAuthentication |
| `resolveOrBuildProjectAuthenticatedUser()` | 5 | Build or use pre-built ProjectAuthenticatedUser |
| `resolveOrBuildRequireFreshMfa()` | 3 | Build or use pre-built RequireFreshMfa |
| `resolveOrBuildGenerateBackupCodes()` | 3 | Build or use pre-built GenerateBackupCodes |
| `resolveOrBuildVerifyBackupCode()` | 3 | Build or use pre-built VerifyBackupCode |
| `resolveOrBuildStartMfaChallenge()` | 7 | Build or use pre-built StartMfaChallenge |
| `resolveProvisionableUserSource()` | 3 | Extract ProvisionableUserSource if available |
| `buildLifecycleOrchestrator()` | 5 | Build LifecycleOrchestrator for SCIM |
| `buildCapabilityReadiness()` | 8 | Build AuthCapabilityReadiness from configured services |
| `buildScimServices()` | ~40 | Build SCIM provisioning/read/bulk services |
| `buildAssessCurrentRisk()` | 7 | Build risk assessment flow |
| `buildReadRiskSignals()` | 5 | Build risk signal reader |
| `buildSessions()` | ~20 | Build Sessions with logout/read/revoke |
| `buildAuthentication()` | ~30 | Build Authentication (login/logout/refresh) |
| `buildAccount()` | ~50 | Build Account (password/email/register) |
| `buildRecovery()` | ~15 | Build password recovery flows |
| `buildVerification()` | ~12 | Build email verification flows |
| `buildMfa()` | ~75 | Build MFA (enroll/verify/recovery/disable) |
| `buildPasskey()` | ~65 | Build Passkey (registration/auth) |
| `buildIdentity()` | ~12 | Assemble final Identity from sub-components |
| `buildScimCapability()` | ~60 | Build SCIM capability with directory management |
| `buildTenancy()` | ~50 | Build Tenancy with tenants and security |

## Metrics

- **Before**: 1 method, 678 lines, 42 constructor params
- **After**: 1 coordinator + 18 private methods, `assemble()` reduced to ~50 lines
- **Cyclomatic complexity**: Reduced from 1 method with score ~40 to 18 methods each with score 1-5
- **Behavior preserved**: All 236 Identity tests pass (same 790 assertions)

## Validation

```text
php vendor/bin/phpunit --no-coverage --filter="Identity" --testdox
Tests: 236, Assertions: 790, Failures: 0, Errors: 0
```

## Risk Assessment

- Zero behavior change — all extracted methods receive explicit dependencies from the coordinator
- The `buildMfa()` and `buildPasskey()` methods received `$projectAuthenticatedUser` as a parameter (was in scope as local variable before)
- No public API changed — `assemble()` return type and behavior are identical
- No files deleted — only existing file was modified
