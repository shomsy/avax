# Runtime Mode

Runtime mode is the phase where the container resolves services from authored registrations and, when available, from the compiled hot path.

## Runtime State

Disposable runtime state lives in:

- attached compiled runtime
- `Runtime/ServicePool.php`
- `DependencyInjection/Scopes/ScopeStore.php`
- lazy-service ledger
- callable cache
- metrics and optional timeline state

This state is disposable. It is not canonical authored truth.

## Hot Path

The production hot path may do:

- alias resolution
- shared lookup
- scoped lookup
- compiled dispatch
- direct instantiation
- injection from compiled metadata
- decoration and extender application
- lifetime storage
- low-overhead metrics and timeline emission

The production hot path must not do:

- reflection
- directory scans
- filemtime scans
- checksum recomputation on every resolve
- full graph validation
- implicit fallback without an explicit policy decision

## Lifecycle Boundaries

- `reset()` clears disposable runtime state and keeps authored registrations plus compiled artifacts
- `flush()` clears disposable runtime state, derived caches, and compiled artifacts while keeping authored registrations
- `flushCompiled()` clears compiled artifacts only

## Runtime Explainability

Use these APIs when the runtime needs to explain itself:

- `describeService()`
- `debugService()`
- `debugPlan()`
- `debugTags()`
- `debugAliases()`
- `debugScope()`
- `runtimeReport()`

`runtimeReport()` summarizes:

- diagnostics mode
- timeline enabled state
- shared and scoped service counts
- deferred provider ownership
- hot-path attachment and freshness
- metrics and timeline data

`describeService()` exposes the per-service compiled decision, cache state, alias chain, decoration chain, and artifact status.
