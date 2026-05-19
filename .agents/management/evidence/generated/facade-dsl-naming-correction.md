# Facade DSL Naming Correction Report

**Date:** 2026-05-19
**Batch:** Production Governance Closure — Facade DSL Naming Correction
**Supersedes:** `hollow-facades-closure.md` (partial correction)
**Stage:** V1 Kernel Green maintenance
**Mode:** Harness-Full

## Executive Summary

The previous hollow facade cleanup (commit `3779fbbf5`) correctly deleted 6 hollow/fake facade classes
but incorrectly renamed `CacheFacade` → `CacheGateway` and `Facade/` → `Gateways/`.

This batch corrects that over-reach:
- Restored `Facade/` directory from `Gateways/`
- Restored `CacheFacade` class name from `CacheGateway`
- Added `Facade` to project naming allowances so thick facades pass governance
- Hollow/fake facades remain deleted — the real problem was preserved

## What the Previous Pass Got Right

1. **ResolveFacade deleted** — 14 lines, 0 refs, trivial array lookup. Correct.
2. **RegisterFacade deleted** — 11 lines, 0 refs, trivial array assignment. Correct.
3. **StorageFacade deleted** — exact duplicate of `Storage.php` in same namespace. Correct.
4. **RequestFacade deleted** — exact duplicate of `Request.php` in same namespace. Correct.
5. **SessionFacade deleted** — exact duplicate of `Session.php` in same namespace. Correct.
6. **CacheContractFacade deleted** — 0 refs, thin `CacheContract` wrapper. Correct.

## What the Previous Pass Got Wrong

### CacheFacade → CacheGateway rename

`CacheFacade` is a real DI-resolved coordinator with:
- Multi-store registry coordination (`CacheRegistry`)
- Compiled cache integration (`CompiledCacheContract`)
- Read strategy routing (`ReadFromCache`)
- 75 lines of real behavior

It is not hollow. It is not a fake OOP class. It is a legitimate public API boundary
that coordinates multiple internal capabilities.

### Why Gateway Was Wrong

`Gateway` is a well-established pattern name for **external/integration boundaries**:
- `StripeGateway` — talks to Stripe's external API
- `SmtpGateway` — talks to an external mail server
- `GitHubGateway` — talks to GitHub's external API

`CacheFacade` does not talk to an external system. It coordinates internal cache stores,
compiled cache, and read strategy. It is a **facade** in the Laravel sense — a public API
boundary that provides a clean DSL over internal complexity.

Renaming it to `CacheGateway` was a mechanical rename that confused two distinct patterns:
- **Facade**: public API boundary over internal complexity (Laravel-style DSL)
- **Gateway**: external system integration boundary

### Facade/ → Gateways/ directory rename

The `Facade/` directory name is a legitimate framework dictionary term. The framework
dictionary (`index.json`) already defines `Facade` as a recognized PHP ecosystem pattern
with clear allowed contexts:
- Static-access proxies to container-resolved services (Laravel style)
- Simplified interfaces to complex subsystems (GoF Facade pattern)
- Developer experience improvements for frequently-used services
- Testing-friendly static access (mockable via container)
- Public API entrypoints that hide complex internal wiring

The `Facade/` directory should remain as `Facade/`.

## Final Class/Folder Naming

| Component | Final Path | Final Class Name | Rationale |
|-----------|-----------|-----------------|-----------|
| Cache DI coordinator | `.../PublicSurface/Facade/CacheFacade.php` | `CacheFacade` | DI-resolved multi-store coordinator, legitimate facade |
| Cache registry | `.../PublicSurface/Facade/CacheRegistry.php` | `CacheRegistry` | Store registry, honest name |
| Static DSL facade | `.../PublicSurface/Cache.php` | `Cache` | Laravel-style static DSL — `Cache::get()`, `Cache::put()` |

## Dictionary/Governance Rule Added

**Location:** `.agents/config/project.json`

```json
"naming_allowances": ["ServiceProvider", "Migration", "Kernel", "Configuration", "Controller", "Application", "Facade"]
```

**Rationale:** `Facade` is a legitimate Laravel-style framework dictionary term.
The governance scanner's thickness audit correctly identifies hollow facades.
But thick facades with real behavior should not trigger BLOCKER — they should
pass via naming allowance while still being subject to the existing
anti-hollow-thickness audit for new classes.

**Governance distinction:**
- `Facade` suffix → allowed via naming allowance (Laravel-style DSL)
- Hollow/empty forwarding class with `Facade` suffix → still caught by thickness audit
- `Gateway` suffix → reserved for external/integration boundaries only
- Never mechanically rename `Facade` to `Gateway`

## Files Changed

### Restored (from previous incorrect rename)
- `components/Application/Cache/System/PublicSurface/Gateways/` → `Facade/` (directory)
- `CacheGateway.php` → `CacheFacade.php` (class and file)
- `CacheGateway` → `CacheFacade` (class name in all references)

### Modified
- `.agents/config/project.json` — added `Facade` to `naming_allowances`
- `.agents/management/evidence/generated/hollow-facades-closure.md` — marked as SUPERSEDED

### Reference updates (5 files)
- `components/Application/Cache/System/Configuration/CacheServiceProvider.php`
- `components/Application/Cache/System/Configuration/CacheRegistrar.php`
- `components/Application/Cache/System/Configuration/Builders/RegisterCacheDependencies.php`
- `components/Application/Cache/System/PublicSurface/Read/ReadFromCache.php`
- `components/Application/Cache/System/PublicSurface/Facade/CacheRegistry.php`
- `components/Application/Cache/System/PublicSurface/Facade/CacheFacade.php`
- `tests/Unit/Components/Application/Cache/PublicCacheClassesAutoloadTest.php`

## Validation Results

| Check | Status |
|-------|--------|
| `verify-governance.sh` | 0 BLOCKER from this batch |
| `check-governance-index-current.php` | GREEN |
| `check-runtime-composition-leaks.php` | PASS |
| `composer validate` | PASS |
| `composer dump-autoload -o` | Generated 9346 classes |
| `phpunit --no-coverage` | 8458 tests, 24330 assertions, 1 pre-existing failure (unrelated) |
| `phpstan analyse` | CLEAN (no errors) |
| `check-component-suite-structure.php` | PASS |
| `check-duplicate-owners.php` | PASS |
| `check-namespace-drift.php` | PASS |
| `check-public-surface.php` | PASS |
| `check-runtime-leaks.php` | PASS |
| `check-component-canonical-shape.php` | GREEN |
| `check-advanced-pattern-folder-violations.php` | GREEN |

## Remaining Issues

### RED
- None from this batch.

### YELLOW / MEDIUM (pre-existing, out of scope)
- Missing 'status' frontmatter in 5 core governance docs
- Forbidden directory 'Docs' at `tooling/Docs`
- Forbidden directory 'Policies' at `examples/Auth/Policies`
- Forbidden directory 'Core' at `tests/docs/Container/Core`
- Forbidden directory 'Repositories' in EVIDENCE backup

### Pre-existing test failure (unrelated)
- `V4DeveloperExperienceCompositionTest::routeCachePlanExists` — missing `EVIDENCE/route-cache-plan.md`

## Evidence Path

`.agents/management/evidence/generated/facade-dsl-naming-correction.md`
