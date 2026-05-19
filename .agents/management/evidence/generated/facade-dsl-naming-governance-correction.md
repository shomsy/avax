# Facade DSL Naming Governance Correction

**Date:** 2026-05-19
**Batch:** Production Governance Closure — Facade DSL Naming (Final)
**Supersedes:** `hollow-facades-closure.md`, `facade-dsl-naming-correction.md`
**Stage:** V1 Kernel Green maintenance
**Mode:** Harness-Full

## Executive Summary

Third and final correction of the hollow facade cleanup chain.

Previous passes correctly deleted 6 hollow/fake facades but had two naming errors:
1. `CacheFacade` — class name redundantly repeats `Facade` when folder already says `Facade/`
2. `CacheGateway` — wrong pattern, Gateway is for external boundaries only

Final resolution:
- `Facade/Cache.php` with `class Cache` — folder says role, class says DSL noun
- Static `Cache.php` at `PublicSurface/Cache.php` remains the user-facing DSL
- Governance rule added to `how-to-write-avax.md` Section 3.1

## Naming Evolution

| Pass | Class Name | File Path | Status |
|------|-----------|-----------|--------|
| Original | `CacheFacade` | `Facade/CacheFacade.php` | BLOCKER — redundant Facade suffix |
| Pass 1 (3779fbbf5) | `CacheGateway` | `Gateways/CacheGateway.php` | WRONG — Gateway is for external boundaries |
| Pass 2 (ac37c215d) | `CacheFacade` | `Facade/CacheFacade.php` | WRONG — redundant Facade suffix in Facade folder |
| Pass 3 (this) | `Cache` | `Facade/Cache.php` | **CORRECT** — folder says role, class says noun |

## What Previous Passes Got Right

1. **6 hollow facades deleted** — ResolveFacade, RegisterFacade, StorageFacade, RequestFacade, SessionFacade, CacheContractFacade
2. **Facade is a dictionary term** — not a forbidden word
3. **Gateway is for external boundaries** — not a mechanical Facade replacement

## What Previous Passes Got Wrong

1. **CacheFacade name** — redundant. Inside `Facade/` folder, class should be short noun `Cache`
2. **CacheGateway name** — wrong pattern entirely. Gateway = external integration boundary
3. **Gateways/ directory** — wrong. Should be `Facade/` (Laravel-style framework term)

## Why CacheGateway Was Wrong

- `Gateway` = external system integration (StripeGateway talks to Stripe's API)
- `Cache` in `Facade/` = internal multi-store coordinator with compiled cache routing
- No external provider, no API translation, no boundary crossing
- The class coordinates internal `CacheRegistry`, `CompiledCacheContract`, and `ReadFromCache`
- Renaming to Gateway confused two distinct architectural patterns

## Why CacheFacade Was Wrong

- Inside `Facade/` folder, the word "Facade" is already expressed by context
- `Facade\Cache` is unambiguous — the namespace provides the role
- `Facade\CacheFacade` is redundant — says "Facade" twice
- AvaX rule: folder says architectural role, class says public DSL noun

## Final Naming

| Component | Path | Class | Purpose |
|-----------|------|-------|---------|
| Static DSL facade | `PublicSurface/Cache.php` | `Cache` | `Cache::get()`, `Cache::put()`, `Cache::remember()` |
| DI-resolved coordinator | `PublicSurface/Facade/Cache.php` | `Cache` | Container-registered multi-store coordinator |
| Store registry | `PublicSurface/Facade/CacheRegistry.php` | `CacheRegistry` | Named cache store registry |

The two `Cache` classes are in different namespaces:
- `Avax\Components\Application\Cache\System\PublicSurface\Cache` (static DSL)
- `Avax\Components\Application\Cache\System\PublicSurface\Facade\Cache` (DI instance)

When both are imported in the same file, the DI instance uses `as CacheInstance` alias.

## Files Changed

### Renamed
- `Facade/CacheFacade.php` → `Facade/Cache.php` (class `CacheFacade` → `Cache`)
- `CacheFacadeTest.php` → `CacheStaticFacadeTest.php` (tests static `Cache::` DSL, not DI class)

### Modified (imports updated)
- `CacheServiceProvider.php` — `CacheFacade` → `Cache as CacheInstance`
- `CacheRegistrar.php` — `CacheFacade` → `Cache as CacheInstance`
- `RegisterCacheDependencies.php` — `CacheFacade` → `Cache as CacheInstance`
- `PublicCacheClassesAutoloadTest.php` — `CacheFacade` → `Cache as CacheInstance`
- `.agents/config/project.json` — removed `Facade` from naming_allowances (no longer needed)
- `.agents/how-to/how-to-write-avax.md` — added Section 3.1 Facade DSL Naming rule

### Evidence updated
- `.agents/management/evidence/generated/facade-dsl-naming-correction.md` — superseded
- `.agents/management/evidence/generated/hollow-facades-closure.md` — superseded

## Governance Rule Added

**Location:** `.agents/how-to/how-to-write-avax.md` Section 3.1

```
Facade DSL Naming:
- Facade is an allowed framework dictionary term
- Folder expresses architectural role: Facade/
- Class expresses public DSL noun: Cache, Route, Config, Event, Log, App
- Correct: Facade/Cache.php with class Cache
- Incorrect: Facade/CacheFacade.php (redundant)
- Incorrect: Gateways/CacheGateway.php (wrong pattern)
- Gateway reserved for external/integration boundaries
- Hollow/fake facade behavior forbidden, Facade pattern itself allowed
```

## Validation Results

| Check | Status |
|-------|--------|
| `verify-governance.sh` | 0 BLOCKER from this batch |
| `check-governance-index-current.php` | GREEN |
| `check-runtime-composition-leaks.php` | PASS |
| `composer validate` | PASS |
| `composer dump-autoload -o` | 9346 classes |
| `phpunit --no-coverage` | 8458 tests, 24330 assertions, 1 pre-existing failure |
| `phpstan analyse` | CLEAN |
| `check-component-suite-structure.php` | PASS |
| `check-duplicate-owners.php` | PASS |
| `check-namespace-drift.php` | PASS |
| `check-public-surface.php` | PASS |
| `check-runtime-leaks.php` | PASS |
| `check-component-canonical-shape.php` | GREEN |
| `check-advanced-pattern-folder-violations.php` | GREEN |

## Remaining RED
**None** from this batch.

## Remaining YELLOW (pre-existing)
- 5 missing frontmatter in core governance docs
- 4 forbidden directory names (out of scope)

## Pre-existing Test Failure (unrelated)
- `V4DeveloperExperienceCompositionTest::routeCachePlanExists` — missing `EVIDENCE/route-cache-plan.md`

## Evidence Path
`.agents/management/evidence/generated/facade-dsl-naming-governance-correction.md`
