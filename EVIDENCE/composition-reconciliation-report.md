# Phase 3: Composition-Based Reconciliation Report

Date: 2026-05-08
Status: GREEN
## A. Dependency Proof

- Arrhae imports zero Collection/Json classes (verified by grep).
- Collection composes Arrhae: `__construct(private Arrhae)`, `make()` factory.
- Json composes Arrhae: `__construct(private Arrhae)`, `decode()` factory.
- No DataPipeline trait remains; no shared behavior under Collection/Internal.

## B. Method Parity

All three DSLs share 50 fluent pipeline methods (make, map, filter, reduce, sort, where, etc.).
Collection-only: IteratorAggregate, ArrayAccess (read-only).
Json-only: decode, encode, pretty, path (JSON Pointer), validate.

## C. Dependency Diagram

```
System/Foundation/ (Mutability, Normalization, Failure)
        ↑
  Arrhae (independent)
    ↑          ↑
Collection    Json  (both compose Arrhae)
```

## D. Validation

- Tests: 989 pass, 4003 assertions, 1 skipped.
- PHPStan: 0 new errors (182 pre-existing warnings).
- Governance: namespace-drift PASS, public-surface PASS, runtime-leaks PASS.

## E. Changes

- **Rewritten**: Arrhae.php (independent), Collection.php, Json.php (compose Arrhae)
- **Modified**: 11 files — fixed imports, use `Collection::make()`
- **Moved**: Comparator → Structures/; Foundation → System/Foundation/
- **Deleted**: DataPipeline trait, Collection/Internal/
- **Created**: how-this-works.md, this report

## F. Remaining Risks

1. Zero-byte stubs in `Capabilities/Foundation/` (harmless).
2. 182 pre-existing PHPStan warnings.
3. External `new Collection(array)` callers must migrate to `Collection::make()`.

## G. Next Allowed Action

Run full canonical validation and proceed to next stage.
