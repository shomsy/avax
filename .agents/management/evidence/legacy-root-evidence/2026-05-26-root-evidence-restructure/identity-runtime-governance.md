# Identity Runtime Governance — Evidence

## Scope
Governance hardening pass before Slice 2 rewrite. Establishes architecture safety gates, runtime-state detection, hidden-construction detection, duplicate capability detection, and worker-safety verification for the Identity component.

## Stage
Governance Hardening Pass

## Status
GREEN

---

## Static State Classification

| Class | Static Properties | Has reset() | Status |
|---|---|---|---|
| Auth | $instance (?Auth) | YES (resetInstance) | GREEN |
| JwtAuth | $jwtSigner, $tokenVerifier, $tokenBlacklist | YES (reset) | GREEN |
| Policy | $policyEvaluator, $definitions | YES (reset) | GREEN |
| TenantContext | $context (?TenantContextInterface) | YES (reset) | GREEN |
| ExternalIdentity | $linkStore (?ExternalIdentityLinkStoreInterface) | YES (reset) | GREEN |

All classes with static mutable state now have a reset mechanism. Auth was the gap — `resetInstance()` was added in this pass.

## InMemory Store Reset Coverage

32 InMemory stores scanned. All 32 have `public function reset()`. GREEN.

## Hidden Construction Findings

Architecture test `IdentityHiddenConstructionTest` scans Flows/ and Capabilities/ for instantiation of infrastructure services (`*Store`, `*Engine`, `*Coordinator`, `*Registry`, `InMemory*`) outside of Configuration/Assembly namespaces.

| Finding | Classification |
|---|---|
| Flow classes instantiate value objects (UserId, AuditEvent, exceptions) | GREEN — on allow list |
| Flow classes instantiate same-namespace siblings | GREEN — on allow list |
| Configuration/Assembly classes instantiate stores | GREEN — legitimate construction zone |
| No infrastructure services instantiated in Flows/Capabilities | GREEN |

## Duplicate Class Classification

27 duplicate basenames found. All classified in allow list. Key patterns:

| Basename | Classification |
|---|---|
| User | PublicSurface DTO vs Capability Entity vs Value Object (layering) |
| Identity | Top-level facade vs Auth capability coordinator (layering) |
| Security | Tenancy capability vs Security sub-component (different domains) |
| JwtSigner | Signing vs Verification namespace (different role) |
| AccessToken/RefreshToken | JwtAuth Tokens vs Auth Tokens (different domains) |
| AuthorizationCode* | Tokens capability vs OAuth capability (different domains) |
| BeginAdminElevation/EndAdminElevation | Access Flow vs Tenancy Capability (different domains) |
| Clock | Foundation class vs Foundation/Time class (YELLOW — needs resolution) |
| Tenancy | Capability vs PublicSurface (intentional layering) |

**YELLOW:** `Clock` — declared in both `Auth/System/Foundation/Clock.php` and `Auth/System/Foundation/Time/Clock.php`. This is a genuine duplicate that should be resolved in a future cleanup pass.

## Architecture Test Suite

### Files Created

| File | Purpose | Tests |
|---|---|---|
| `IdentityComponentStructureTest.php` | Verifies canonical component shape, forbids Services/Helpers/Utils etc. | 14 |
| `IdentityStaticStateTest.php` | Detects static mutable state, verifies reset coverage | 2 |
| `IdentityWorkerSafetyTest.php` | Behavioral reset verification, Auth resetInstance, InMemory spot checks | 6 |
| `IdentityHiddenConstructionTest.php` | Detects hidden infrastructure construction in Flows/Capabilities | 1 |
| `IdentityDuplicateClassTest.php` | Detects duplicate class declarations with allow list | 3 |

### Test Results

```
OK (26 tests, 58 assertions)
```

### PHPStan Results

All new test files pass PHPStan level 8.
Only 2 pre-existing issues remain in Identity components:
- `PolicyEvaluator.php:41` — type mismatch on $reasons (MEDIUM, pre-existing)
- `AssembleAuthExternalIdentityGraph.php:214` — dead Diagnostics instantiation (INFO, pre-existing)

## Worker Safety Proof

1. **Auth::resetInstance()** — Added. Clears static `$instance` reference. Test verifies `instance()` throws after reset.
2. **All InMemory stores** — 32/32 have `public function reset()`. Behavioral tests verify 3 stores (UserSource, SessionRegistry, CredentialStore) actually clear state after reset.
3. **Static facades** — JwtAuth, Policy, TenantContext, ExternalIdentity all have `public static function reset()`.

## Risk Assessment for Slice 2

### GREEN
- All static mutable state has reset mechanisms
- All InMemory stores are resettable
- Architecture tests will catch new violations
- Hidden construction detection guards Flows/Capabilities
- Duplicate class detection prevents accidental duplication
- Worker safety tests verify reset behavior

### YELLOW
- `Clock` duplicate in Auth/Foundation — should be resolved in cleanup pass
- `Auth::$instance` static singleton — now has reset but ideally should be fully DI-injected (deprecated pattern persists)
- Deprecated static facades (Credentials, ExternalIdentity, TenantContext) — still present but have reset escape hatches

### No BLOCKER/HIGH
- No unreset static state
- No hidden infrastructure construction in business logic
- No unexpected duplicate classes

---

## Suppression Check

No suppressions added. No baseline entries added. No test skips. No weakened assertions.

---

## Why This Is GREEN

- **validation:** PHPUnit 26/26 GREEN, PHPStan clean on all new files, git diff --check clean
- **gates:** No BLOCKER/HIGH findings introduced
- **deviation_audit:** 1 YELLOW (Clock duplicate, pre-existing), all else GREEN
- **corrections:** Auth::resetInstance() added to close worker-safety gap
- **remaining_deviations:** 1 YELLOW (Clock duplicate), 2 pre-existing PHPStan issues (MEDIUM + INFO)
- **suppression_check:** No suppression detected
- **risk_assessment:** All changes are additive (tests + one method). No API breaks. No behavioral changes to production code beyond adding resetInstance().
- **severity_decision:** YELLOW status for Clock duplicate is appropriate — it's a known issue with clear classification, not a security or runtime safety risk.

---

## Files Changed

| File | Change |
|------|--------|
| `components/Identity/Auth/System/PublicSurface/Auth.php` | Added `resetInstance()` method |
| `tests/Architecture/Components/Identity/IdentityComponentStructureTest.php` | New — component shape tests |
| `tests/Architecture/Components/Identity/IdentityStaticStateTest.php` | New — static state detection |
| `tests/Architecture/Components/Identity/IdentityWorkerSafetyTest.php` | New — worker safety verification |
| `tests/Architecture/Components/Identity/IdentityHiddenConstructionTest.php` | New — hidden construction detection |
| `tests/Architecture/Components/Identity/IdentityDuplicateClassTest.php` | New — duplicate class detection |

---

## Next Allowed Action

Commit governance hardening. Identity is now governance-protected before Slice 2 expansion.
