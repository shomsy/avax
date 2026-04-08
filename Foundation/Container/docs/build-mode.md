# Build Mode

Build mode is the phase where the container turns authored definitions into generated artifacts.

## Source Of Truth

Canonical authored truth lives in:

- `DependencyInjection/Dependencies/Bindings/ServiceRegistry.php`
- explicit registrations
- aliases
- tags
- contextual rules
- decorators and extenders
- deferred ownership declarations

Compiled artifacts are generated outputs, not source of truth.

## What Build Mode Produces

Build mode can generate:

- compiled container PHP at `compiled/container.php`
- compiled container metadata JSON at `compiled/container.json`
- blueprint cache artifacts
- typed `CompileReport` output

The metadata carries:

- format and schema version
- cache version
- config hash
- settings fingerprint
- environment
- compile mode
- diagnostics mode
- strictness
- dependency graph revision
- artifact paths
- warmed flag
- optional benchmark build marker
- per-service signatures
- invalidation and reuse statistics

## Determinism Rules

Build mode must keep deterministic ordering for:

- service ids
- alias map keys
- tag index keys
- lifetime plans
- decoration counts
- dependency graph edges

The same authored input and the same config should produce the same compiled fingerprint.

## Invalidation Rules

Rebuild or invalidate when any of these change:

- service signature
- dependency graph
- config hash
- environment
- compile mode
- diagnostics mode
- strictness
- cache version
- artifact schema version

## Operator Surface

- `compileContainer()`
- `warmCompiled()`
- `flushCompiled()`
- `rebuildCompiled()`
- `compileReport()`

Use `compileReport()` as the operator-facing explanation surface for artifact availability, compatibility, freshness, invalidation, and reuse.
