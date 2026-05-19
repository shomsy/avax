# 11 — Root Container Ownership Truth

**Date**: 2026-05-16
**Stage**: V5.9 Boot DSL First Slice
**Status**: YELLOW (accepted debt)

## Finding (from 07-correction-preflight)

The `BootDslEngine` manually constructs the `Runtime` object graph and service dependencies (`HandleIncomingHttp`,
`CreateHttpResponse`, etc.) instead of delegating to a full DI container. This is expected for a first slice using
`SimpleContainer`/`FrozenContainer`, but is YELLOW debt that should be migrated when full `DIContainer` is available.

## Current State

- `BootDslEngine` receives dependencies via constructor injection (not container resolution)
- `BootDslEngine` creates `FrozenContainer` and binds primitives as instances
- Runtime services (`HandleIncomingHttp`, `ComponentRegistry`, etc.) are manually instantiated
- Provider registration/boot goes through the container, but core wiring does not

## Why This Is Acceptable for First Slice

1. `SimpleContainer` is intentionally minimal — no auto-wiring, no reflection-based resolution
2. `DIContainer` depends on unimplemented `RegisterDependencies` flow
3. Full container wiring requires V5.9+ scope and should be a separate milestone
4. The container lifecycle (register → compile → verify → boot → freeze) is proven correct regardless

## Migration Target

When `DIContainer` is available:

- Replace `FrozenContainer` with DIContainer-based frozen wrapper
- Move manual instantiation to container bindings/providers
- Keep the same phase lifecycle and freeze semantics

## Proof

- All 25 tests pass — behavior is correct even with manual wiring
- `repeated_boot_does_not_leak_state` — proves isolation between boots
- `boot_dsl_engine_advances_through_all_phases` — proves full lifecycle works

## Verdict

YELLOW — accepted debt with documented migration target. Not a bug. First slice scope is correct.
