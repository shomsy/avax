# V5.7 Real Dogfooding — Final Report

**Date:** 2026-05-13
**Branch:** main
**Stage:** V5.7-09 through V5.7-12
**Status:** GREEN

## Summary

V5.7 Real Dogfooding is complete. The canonical AvaX Events runtime is proven in a real reference flow.

## Stages Completed

| Stage                                 | Status               | Evidence                                   |
|---------------------------------------|----------------------|--------------------------------------------|
| V5.7-09 Real Event Dogfooding         | GREEN                | 43-real-event-dogfooding.md                |
| V5.7-10 CQRS Projection Dogfooding    | GREEN                | 44-cqrs-projection-dogfooding.md           |
| V5.7-11 Event-History Reference Proof | GREEN_REFERENCE_ONLY | 45-event-history-reference-proof.md        |
| V5.7-12 Container Event Readiness     | READINESS_ONLY       | 46-container-event-dogfooding-readiness.md |

## Dogfooding Evidence

- SecureRegistrationApi emits `UserRegistered` after successful registration
- `onEvent(UserRegistered::class)->do(...)` registers 3 listeners
- `RecordRegistrationAudit` — records audit data
- `ProjectRegisteredUser` — builds CQRS projection
- `RecordUserRegisteredEvent` — stores event in history
- `RegisteredUserView` — in-memory read model
- `ReadRegisteredUser` — query side
- `ReferenceEventHistoryStore` — minimal event-history store
- `ReplayEventHistory` — rebuilds projection from stored events

## Tests

- 12 new dogfooding tests, 37 assertions — all GREEN
- 84 total Events tests — all GREEN
- 8069 total framework tests — all GREEN

## Event Gates

All 10 event gates PASS:

1. check-canonical-event-owner.php — PASS
2. check-fluent-dsl-registration.php — PASS
3. check-event-emission-api.php — PASS
4. check-listens-to-attribute.php — PASS
5. check-compiled-listener-registry.php — PASS
6. check-dispatch-runtime.php — PASS
7. check-psr14-interop.php — PASS
8. check-events-no-hot-path-reflection.php — PASS
9. check-real-dogfooding.php — PASS
10. check-event-sourcing-not-default.php — PASS

## No Interface Requirement

- User events are plain objects — no EventInterface required
- User listeners are plain invokable classes — no ListenerInterface required

## No Production Event Sourcing Claim

- Event-history is reference/proof only
- No EventStore, stream versioning, snapshots, or replay engine
- Production Event Sourcing Kit = ROADMAP

## No Hot-Path Reflection

- Runtime dispatch (EventEmitter::emit) uses no reflection
- Compile path (CompileEventListeners) uses reflection for #[ListensTo] — documented and correct

## Next Allowed Action

V5.7-13: Final Acceptance Audit — close V5.7 stage.
