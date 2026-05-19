# V5.8.9 AuthBuilder Constructor Drift & Runtime Gate Closure — Final Summary

## Stage: V5.8.9 Hardening Pass

## Status: COMPLETE / GREEN

## Goal

Fix 3 DispatchConfiguredRoute runtime composition leaks, ~184 AuthBuilder constructor drift PHPStan errors, and verify GraphQLSchema runtime assembly — reducing PHPStan from 253 to 63 errors.

## Scope

### Allowed

- [x] Fix runtime composition leaks in DispatchConfiguredRoute (3 findings)
- [x] Fix AuthBuilder constructor drift (~184 PHPStan errors)
- [x] Verify GraphQLSchema runtime assembly (0 findings confirmed)
- [x] Fix related constructor drift in dependent capability classes
- [x] Update evidence and truth files

### Forbidden

- [x] No V5.9 Boot DSL implementation
- [x] No new feature behavior
- [x] No broad refactor
- [x] No ?? new fallbacks
- [x] No private default factories
- [x] No weakening constructors
- [x] No broad PHPStan ignores
- [x] No test weakening

## Files Changed

### Framework
- `framework/System/Flows/HandleIncomingHttp/DispatchConfiguredRoute.php` — Stripped to pure runtime execution
- `framework/System/Configuration/BuildDispatchConfiguredRoute.php` — New: assembly builder
- `framework/System/Configuration/BuildApplication/Builders/ApplicationBuilder.php` — Updated to call BuildDispatchConfiguredRoute
- `framework/System/Flows/createResponse/CreateResponse.php` — Fixed html/text parameter names

### Components
- `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` — Fixed ~184 constructor drift errors
- `components/Identity/Auth/System/Flows/Login/Login.php` — Identity → IdentityInterface
- `components/Identity/Auth/System/Flows/Login/FindUserByCredentials.php` — UserSource → UserSourceInterface
- `components/Identity/Auth/System/Flows/Logout/Logout.php` — Identity → IdentityInterface
- `components/Identity/Auth/System/Flows/Register/Register.php` — Identity → IdentityInterface
- `components/Identity/Auth/System/Flows/Register/CreateRegisteredUser.php` — Array → User object, added IdGeneratorInterface
- `components/Application/Cache/System/Configuration/CacheRegistrar.php` — Fixed BuildCache constructor
- `components/Application/DateTime/System/Configuration/Builders/RegisterDateTimeServices.php` — Removed non-existent method call
- `components/Application/DateTime/System/Configuration/Builders/RegisterDateTimeDependencies.php` — Removed non-existent method call

### Evidence
- `EVIDENCE/hardening/43-v5-8-9-preflight.md`
- `EVIDENCE/hardening/44-v5-8-9-worktree-baseline.md`
- `EVIDENCE/hardening/45-v5-8-9-blocker-inventory.md`
- `EVIDENCE/hardening/46-dispatch-configured-route-runtime-leak-closure.md`
- `EVIDENCE/hardening/47-auth-builder-constructor-drift-remediation.md`
- `EVIDENCE/hardening/48-graphql-schema-runtime-assembly-verified.md`
- `EVIDENCE/hardening/49-phpstan-reduction-253-to-63.md`
- `EVIDENCE/hardening/50-v5-8-9-full-validation.md`
- `EVIDENCE/hardening/51-v5-8-9-governance-review.md`
- `EVIDENCE/hardening/52-v5-8-9-truth-reconciliation.md`
- `EVIDENCE/hardening/53-v5-8-9-final-summary.md`

### Truth Files
- `CURRENT_TRUTH.md` — Added V5.8.9 section

## Files Intentionally Not Touched

- Test files (no test weakening)
- GraphQL schema files (already clean)
- OAuth client registry classes (only AuthBuilder calls fixed)
- Passkey store classes (only AuthBuilder calls fixed)
- TenantSecurity store classes (only AuthBuilder calls fixed)

## Validation Commands

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
```

## Validation Result

| Metric | Before | After |
|--------|--------|-------|
| PHPStan errors | 253 | 63 |
| PHPUnit tests | 8351 | 8351 |
| PHPUnit assertions | 24026 | 24026 |
| PHPUnit failures | 0 | 0 |
| Runtime composition leaks | 3 | 0 |
| Runtime assembly findings | 0 | 0 |
| Architecture checks | GREEN | GREEN |

## Evidence

- PHPStan: 63 errors (all pre-existing, 190 fixed by this pass)
- PHPUnit: 8351 tests, 24026 assertions, 0 failures, 1 deprecation (pre-existing)
- Runtime composition gate: 0 findings
- Runtime assembly gate: 0 findings
- All architecture checks: GREEN

## Key Changes Summary

### Phase A: DispatchConfiguredRoute Runtime Leak Closure
- Stripped static factory methods from DispatchConfiguredRoute (Flows/)
- Created BuildDispatchConfiguredRoute (Configuration/Builders/)
- Updated ApplicationBuilder to call BuildDispatchConfiguredRoute directly
- Result: 3 runtime composition leaks → 0

### Phase B: AuthBuilder Constructor Drift Remediation
- Fixed ~184 PHPStan errors across all constructor calls in AuthBuilder
- Updated Login/Logout/Register to instantiate capability classes
- Fixed type hints: Identity → IdentityInterface, UserSource → UserSourceInterface
- Fixed CreateRegisteredUser: array → User value object with UserId/UserEmail
- Result: ~184 errors → 0

### Phase C: GraphQLSchema Runtime Assembly Verification
- Verified no runtime assembly violations in GraphQL schema
- Result: 0 findings (confirmed clean)

### Phase D: PHPStan Reduction
- Total fixed: 190 errors
- Remaining: 63 pre-existing (array types, mixed variables, typePerfect, test assertions)
- Result: 253 → 63 errors

## Remaining Risks

1. **63 pre-existing PHPStan errors** — Not introduced by V5.8.9. Should be addressed in a focused cleanup pass.
2. **1 PHPUnit deprecation** — Pre-existing, not related to V5.8.9 changes.

## Next Allowed Stage

V5.9 Boot DSL — Ready to begin pending decision on remaining 63 PHPStan errors (baseline vs cleanup first).

## Done Definition

- [x] DispatchConfiguredRoute runtime leaks: 0
- [x] AuthBuilder constructor drift: 0 PHPStan errors
- [x] GraphQLSchema runtime assembly: 0 findings
- [x] PHPStan reduced from 253 to 63
- [x] All validation commands pass (except pre-existing 63 PHPStan errors)
- [x] Evidence files 43-53 created
- [x] CURRENT_TRUTH.md updated
- [x] Governance review complete
- [x] No forbidden scope changes
- [x] No test weakening
- [x] No ?? new fallbacks
- [x] No weakened constructors
