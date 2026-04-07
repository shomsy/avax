# Container Refactor Status

## Shipped

- `Compilation/` and `Runtime/` now exist as first-class owners.
- `compileContainer()`, `warmCompiled()`, `flushCompiled()`, and `rebuildCompiled()` are shipped.
- `alias()`, `tagged()`, `decorate()`, and `lazy()` are shipped on the public facade.
- `flush()` and `reset()` are shipped for deterministic lifecycle cleanup.
- `defer()`, provider composition, and `env()` hooks are shipped.
- `forContext()`, `validate()`, `describeService()`, and debug helpers for plans, tags, aliases, and scope state are shipped.
- compiled blueprints, compiled resolve plans, and generated hot-path runtime dispatch are shipped.
- singleton storage is split into `Runtime/ServicePool.php`; scoped storage stays in `DependencyInjection/Scopes/ScopeStore.php`.
- benchmark harnesses exist for cold, warm, hot, deep, wide, scoped, lazy, and compile-time paths.

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
