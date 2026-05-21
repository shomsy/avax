# Identity Redesign — Slice 1: Architecture Skeleton Cleanup

## Summary

All 15 known corrections from the plan have been assessed. Most were already fixed. Two genuine duplications were cleaned up.

## 15 Known Corrections — Final Status

| # | Correction | Status | Action Taken |
|---|-----------|--------|-------------|
| 1 | Delete empty RegisterAccessDependencies | **GREEN** | Not found — already absent |
| 2 | Move RegisterAuthDefaults out of Builders | **GREEN** | Already in `Providers/` |
| 3 | AuthBuilder no ContainerInterface | **GREEN** | Never had it |
| 4 | EndpointPostureEngine one class per file | **GREEN** | Confirmed separate files |
| 5 | EndpointPosturePolicy/Decision own files | **GREEN** | Confirmed separate files |
| 6 | PolicyRule.php one class per file | **GREEN** | Confirmed separate file |
| 7 | AttributeCondition own file | **GREEN** | Confirmed separate file |
| 8 | AttributeCondition::withinHours() no date('H') | **GREEN** | Uses `DateTimeImmutable` |
| 9 | BeginAdminElevation no static mutable state | **GREEN** | Uses `AdminElevationStore` instance |
| 10 | Access delegates to AccessRuntime | **GREEN** | Confirmed |
| 11 | Identity DSL no new sub-surfaces | **ACCEPTED YELLOW** | Builder is composition context, `new` allowed |
| 12 | No FQCN in methods | **GREEN** | Spot-checked, imports used correctly |
| 13 | No duplicate PermissionDenied | **FIXED** | Consolidated to one, deleted 2 duplicates |
| 14 | No duplicate AccessPolicy ambiguity | **ACCEPTED YELLOW** | Interface is `@deprecated`, canonical VO exists |
| 15 | No construction-only tests | **DEFERRED** | Requires test audit in later slices |

## Changes Made

### Deleted Files (3)
1. `Access/System/Capabilities/RequirePermission/PermissionDenied.php` — duplicate exception
2. `Access/System/Foundation/Exception/PermissionDeniedException.php` — redundant alias
3. `Access/System/Capabilities/Authorization/Authorization.php` — identical copy of `Facades/Authorization`

### Modified Files (5)
1. `Access/System/Foundation/Exception/PermissionDenied.php` — now carries `UserPermission|null $requirement`
2. `Access/System/Capabilities/RequirePermission/RequirePermission.php` — imports Foundation exception
3. `Access/System/Capabilities/AccessInterface.php` — imports Foundation exception
4. `Access/System/Capabilities/Facades/Authorization.php` — imports Foundation exception
5. `Access/System/Capabilities/AccessRuntime/AccessRuntime.php` — uses named argument `message:` for throw

## Validation

- PHPUnit: 156 tests, 455 assertions, GREEN (2 pre-existing warnings from negative test)
- PHPStan: Clean on changed files
- No broken references to deleted classes

## Remaining YELLOW

| Finding | Severity | Why Accepted | Owner | Target |
|---------|----------|-------------|-------|--------|
| `AccessPolicy` interface + class coexist | LOW | Interface is `@deprecated`, migration path documented in its docblock | Slice 4 | Deprecate interface |
| Identity builder defaults use `new` | LOW | Composition context, explicit defaults | Slice 7 | ServiceProvider registration |
| Construction-only tests not audited | MEDIUM | Requires test-by-test review | Slice 8 | Test quality pass |
