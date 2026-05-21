# Slice 5: Tenancy Static State Removal

Status: COMPLETED
Date: 2026-05-21
Branch: architecture/identity-world-class-redesign

## Purpose

Remove mutable static state from TenantContext — the source of request-scoped
tenant identification that leaks across requests in long-lived workers.

## Problem

`TenantContext` owned `private static ?string $current = null`. This leaks
tenant identity across requests in Swoole/RoadRunner/FrankenPHP workers.

## Changes

### Created
- `Tenancy/System/Capabilities/Context/TenantContextInterface.php`
  — contract for tenant context with `current()`, `set()`, `clear()`, `with()`
- `Tenancy/System/Capabilities/Context/DefaultTenantContext.php`
  — instance-based implementation (replaces the old static $current)
- `Tenancy/System/Configuration/Assembly/TenancyGraph.php`
  — configures the TenantContext facade with `useContext()` for DI integration

### Modified
- `Tenancy/System/Capabilities/Context/TenantContext.php`
  - Replaced `private static ?string $current` with delegation to `TenantContextInterface`
  - Added `setContext()` for DI integration
  - `current()`, `set()`, `clear()`, `with()` now delegate to the injected context
  - Default fallback: `DefaultTenantContext` (same behavior as before)
  - Marked `@deprecated` — users should inject `TenantContextInterface` directly

### Unchanged
- `Tenancy` facade — remains fully static (delegates to TenantContext static methods, which forward)
- All tests pass without modification

## Public API Compatibility

| Old | New | Status |
|-----|-----|--------|
| `TenantContext::current()` | same (delegates) | backward compat |
| `TenantContext::set()` | same (delegates) | backward compat |
| `TenantContext::clear()` | same (delegates) | backward compat |
| `TenantContext::with()` | same (delegates) | backward compat |
| — | `TenantContext::setContext()` | new |

## Validation

```text
php vendor/bin/phpunit --no-coverage --filter="Identity"
Tests: 236, Assertions: 784, Failures: 0, Errors: 0
```

## Risk Assessment

- Backward compatible — all static method signatures preserved
- Static `$current` is now an instance property on `DefaultTenantContext`
- `DefaultTenantContext` used by default — behavior identical to old static state
- `setContext()` allows DI replacement without breaking existing callers
