# Container Refactor Status

## Shipped

- `Compilation/` and `Runtime/` now exist as first-class owners.
- `compileContainer()`, `warmCompiled()`, `flushCompiled()`, and `rebuildCompiled()` are shipped.
- `alias()`, `tagged()`, `decorate()`, and `lazy()` are shipped on the public facade.
- `flush()` and `reset()` are shipped for deterministic lifecycle cleanup.
- `defer()`, provider composition, and `env()` hooks are shipped.
- `forContext()`, `validate()`, `describeService()`, `debugService()`, and debug helpers for plans, tags, aliases, and scope state are shipped.
- `hasAlias()`, `isDeferred()`, `isLazy()`, `isCompiled()`, and `isWarmedUp()` are shipped on the public facade.
- compiled blueprints, compiled resolve plans, and generated hot-path runtime dispatch are shipped.
- compiled container metadata now records checksum, config hash, environment, changed service ids, and per-service signatures.
- compiled container metadata is now a first-class contract via `Compilation/ArtifactMetadata.php`.
- `compileReport()` and `runtimeReport()` now expose typed machine-readable build/runtime reports.
- deterministic provider ordering is now owned by `DependencyInjection/Dependencies/Providers/ProviderBootPlan.php`.
- runtime lifetime decisions are now owned by `DependencyInjection/Dependencies/Resolution/LifetimePlan.php`.
- diagnostics mode is now explicit via `minimal` and `detailed` runtime behavior.
- deferred providers now stay out of eager boot and register or boot on first owned service resolve.
- incremental compile reuse now records reused services, invalidated services, dependency graphs, and invalidation reasons.
- compiled artifact corruption now quarantines bad files; production-style compile modes fail closed and dev mode falls back dynamically.
- singleton storage is split into `Runtime/ServicePool.php`; scoped storage stays in `DependencyInjection/Scopes/ScopeStore.php`.
- benchmark harnesses exist for cold, warm, hot, worker, request lifecycle, deep, wide, scoped, lazy, deferred service, deferred provider, call, property injection, method injection, and compile-time paths.
- benchmark regression guard exists under `tests/check-benchmarks.sh`.
- benchmark artifact comparison now exists under `tests/run-benchmark-comparison.sh` and `tests/benchmarks/compare.php`.
- benchmark guard now uses repeated runs with median timing, and benchmark comparison no longer writes generated artifacts into the repository tree.

## Completed

- Lifecycle control
- Deferred services and provider composition
- Context and diagnostics
- Performance gates

## Rules

- One concept = one name.
- No legacy shadow architecture.
- Compiled artifacts are generated outputs, not source of truth.
- Public DX must stay ahead of internal theory.
