# ADR-002: Compile/Runtime Model

- Status: Accepted
- Date: 2026-04-08

## Context

The container already ships a real compile/runtime path, deferred providers, typed reports, and generated artifacts. The remaining risk is not feature breadth. The risk is ownership drift:

- authored definitions, compile metadata, and runtime caches can be treated as one mutable blob
- `CreateContainer` can become the next assembler god-object
- artifact reuse can look "available" even when the current config or runtime mode is incompatible

This component needs one explicit rule that stays true under refactors and pressure.

## Decision

The container uses this model:

- reflect once
- compile once
- run many times

Ownership is split like this:

- authored definitions live in `src/Capabilities/Declaration/Bindings/ServiceRegistry.php`
- derived registration caches also live in `ServiceRegistry`, but only behind `resetDerivedState()`
- compiled artifact lifecycle lives in `src/Capabilities/Composition/Compilation/CompileContainer.php`
- compiled runtime attachment and hot-path reuse live in `src/Capabilities/Runtime/CompiledRuntime.php`
- deferred provider ownership and lazy boot live in `src/Capabilities/Declaration/Providers/DeferredProviderRegistry.php`
- scope-local and shared runtime instances live in `src/Capabilities/Runtime/Scopes/` plus `src/Capabilities/Runtime/ServicePool.php`
- assembly stays under `src/Flows/CreateContainer/CreateContainer.php`, with internal collaborator wiring split under `src/Capabilities/Composition/Assembly/`

Lifecycle rules are explicit:

- `flush()` clears derived caches, runtime state, and compiled artifacts
- `reset()` clears disposable runtime state only
- neither operation mutates canonical authored registrations

Artifact compatibility is explicit:

- cache version, config hash, environment, compile mode, and strictness are part of compatibility
- incompatible artifacts are not reported as available
- corrupt artifacts follow the corruption policy and quarantine path
- compatibility mismatch falls back to dynamic runtime instead of pretending the compiled path is valid

Mode policy stays explicit:

- `dev`: validate on load and allow dynamic fallback for stale or incompatible artifacts
- `production`: prefer the compiled hot path and fail closed on corruption
- `ci` and `warmup`: validate before compile and reject invalid graphs before artifacts are accepted

## Consequences

- canonical registrations remain stable after bootstrap unless the caller explicitly rewrites them
- compile metadata is rebuildable and disposable
- runtime state is disposable and safe to reset between worker jobs or tests
- `CreateContainer` stays the single flow owner without inlining every collaborator constructor
- docs, reports, and smoke tests can talk about authored state, compiled state, and runtime state without ambiguity
