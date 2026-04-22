# Runtime State Model

Runtime state is disposable by design.

## Owned Runtime State

- attached compiled runtime inside `CompiledRuntime`
- shared instances inside `ServicePool`
- scoped instances inside `ScopeStore`
- lazy markers inside `ServiceResolver`
- callable cache inside `FunctionCaller`
- low-overhead counters inside `ResolutionMetrics`
- optional timeline events inside `ResolutionTimeline`

None of this is authored truth.

## Boundaries

- authored registrations live in `ServiceRegistry`
- compile outputs live under the compiled artifact directory and blueprint cache
- runtime state is cleared by `reset()` and can be rebuilt from authored registrations plus optional compiled artifacts

## Lifecycle Semantics

- worker start: container may attach a compiled artifact and fill shared scope state
- operation start: open an `operation` scope or use `withinScope(...)`
- request start: open a `request` scope when request-bound services exist
- job start: open a `job` scope when worker/job-bound services exist
- tenant start: open a `tenant` scope when tenant-bound services exist
- request end: close the scope so scoped instances are dropped
- worker boundary: call `reset()` to clear shared instances, scoped frames, lazy markers, telemetry state, and callable
  caches
- full runtime clear: call `flush()` when you also want compiled artifacts and derived caches gone

Shared services can now carry explicit lifecycle modifiers:

- `warm`: eager shared warmup during `warmCompiled()`
- `lazy`: shared singleton that stays out of warmup until first resolve
- `disposable`: shared or scoped instance whose disposal is owned by the runtime boundary

## Runtime Report Contract

`RuntimeReport` is the machine-readable runtime state surface. It is versioned and exposes:

- `schemaVersion`
- registration revision and compiled revision
- compiled attachment and warmup state
- diagnostics mode and whether timeline capture is enabled
- shared and scoped service counts
- aliases and deferred provider owners
- metrics and timeline arrays
- scope snapshot summary
- hot-path summary
- the current `CompileReport`

## Worker And Request Safety

The runtime stays disposable between jobs because:

- shared instances are owned by runtime storage, not the registry
- scope frames are explicit and can be terminated in one call
- compiled artifacts survive `reset()`, so workers can keep warmup state without leaking resolved objects across jobs
- ownership rules stay authored in registrations, so resetting runtime state never loses slice visibility truth

See [`worker-request-lifecycle.md`](./worker-request-lifecycle.md) for the operator view.
See [`lifetimes-and-scopes.md`](./lifetimes-and-scopes.md) for the full lifetime model.
