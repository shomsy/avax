# V5.7-12: Container Event Dogfooding Readiness

**Date:** 2026-05-13
**Branch:** main
**Stage:** V5.7-12 Container Event Dogfooding Readiness
**Status:** READINESS_ONLY — NOT IMPLEMENTED

## Container Lifecycle Event Inspection

Inspected whether Container lifecycle event dogfooding is safe:

- `ContainerCompiled`
- `ServiceResolutionFailed`

## Assessment

Container event dogfooding is NOT safe at this time because:

1. **Dependency graph unclear**: The Container's lifecycle events are not yet fully defined as part of the Events
   runtime.
2. **EventDispatcher ↔ Container cycle risk**: Wiring Container events through the Events runtime could create a
   circular dependency if the Container is needed to resolve listeners.
3. **No ServiceResolved emission**: `ServiceResolved` is NOT emitted by default in the hot path (correct — would be a
   performance concern).

## Decision

Container lifecycle event dogfooding is deferred. Readiness evidence only.

## Conditions for Future Implementation

- Container must define explicit lifecycle events
- No circular dependency between Container and EventEmitter
- Events must not be emitted in Container hot path (resolve, make, get)
- Must use compiled registry (no runtime reflection)

## Next Allowed Action

Complete V5.7 Tooling Gates & Acceptance Audit.
