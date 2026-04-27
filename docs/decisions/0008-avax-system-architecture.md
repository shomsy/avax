# ADR 0008: Avax Framework System Architecture

## Status

Accepted

## Context

`framework/System` owns the runtime lifecycle for Avax. It must be runtime-agnostic - PHP-FPM, RoadRunner, FrankenPHP, Swoole, Workerman must not leak into core components.

## Decision

`framework/System` is structured as:
- `PublicSurface/` - stable entrypoints
- `Flows/` - end-to-end behavior
- `Capabilities/` - reusable mechanisms
- `Configuration/` - assembly/wiring
- `Foundation/` - tiny neutral primitives

## Consequences

- Framework lifecycle is explicit and testable
- Runtime adapters live in `Capabilities/Runtime/Adapters/`
- Core components remain reusable outside any specific runtime
- Request state is scoped to `RequestScope`
- State reset is enforced for worker safety

## Non-Negotiable Rules

1. No runtime-specific API in core components
2. Request-specific state must live in explicit request scope
3. PublicSurface must delegate, not execute internals
4. Foundation must stay small and neutral
5. Every increment must leave the repository runnable