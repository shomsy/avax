# Stage C DI and Runtime Assembly Discipline

Date: 2026-05-14
Status: GREEN

## Fixed Now

- **C-A.03**: `Runtime::runWorker()` — added `HandleIncomingHttp|null $handleIncomingHttp` as injectable constructor
  parameter. Composition roots can now provide a properly configured `HandleIncomingHttp` instead of relying on
  `new ResponseFactory()` inside the hot path.
- **C-A.04**: `App.php` — already clean (no `new ResponseFactory` inside)
- **C-B**: All framework Flows with `= new ClassName()` defaults already fixed by previous Phase C work
  (`HandleRuntimeFailure`, `ValidateConfig`, `CheckRuntimeIsolation`, `DiscoverComponents`, etc.)
- **C-C/C-D**: Composition roots in Flows and `new` in Closures already addressed
- **C-E**: Most PublicSurface static facades already refactored; remaining instances are in builders/Configuration

## Remaining (Deferred — Architecturally Acceptable)

- **C-G**: `AuthBuilder.php` — 32 `?? new` fallbacks. Acceptable: this is a Configuration/Builder class (composition
  root). The fallbacks provide DX defaults for optional features.
- **C-F.01-C-F.03**: Cache component `?? new` patterns in `RememberCachedValue`, `FileCacheStore`. Acceptable: these are
  internal capability defaults, not public API leaks.
- **C-F.19**: `QueueState.php` — `InMemoryFailedJobsStore` fallback. Acceptable: in-memory default for queue state.

## Validation

| Gate                                   | Result                               |
|----------------------------------------|--------------------------------------|
| `vendor/bin/phpunit --no-coverage`     | GREEN (8293 tests, 23811 assertions) |
| `check-component-runtime-assembly.php` | GREEN (3139 files scanned)           |
| `check-runtime-leaks.php`              | PASS                                 |
| `check-public-surface.php`             | PASS                                 |
