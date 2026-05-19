# V5.7-53: Events Test Coverage Final Audit

**Date:** 2026-05-13
**Branch:** main

## Test Coverage Summary

### Existing Events Tests (EventsRuntimeClosureTest.php)

- 49 existing tests covering:
    - emit() public surface (V5.7-04)
    - ListensTo attribute (V5.7-05)
    - Compiled listener registry (V5.7-06)
    - Dispatch runtime (V5.7-07)
    - PSR-14 adapter (V5.7-08)
    - No hot-path reflection
    - No EventInterface/ListenerInterface requirement
    - Priority ordering
    - Same-priority ordering
    - Listener failure bubbling
    - Stoppable events
    - DSL + attribute shared registry
    - ResolveEventListeners capability
    - InvokeEventListener capability
    - CompileEventListeners flow
    - CompiledListenerRegistry utilities
    - RegisterEventDependencies

### New Dogfooding Tests (EventsDogfoodingTest.php)

- 12 tests covering:
    - registration emits UserRegistered
    - audit listener records expected audit
    - projection listener builds RegisteredUserView
    - ReadRegisteredUser returns projection
    - without listener registration, projection is not created
    - event-history stores UserRegistered event
    - event-history replay rebuilds RegisteredUserView
    - event-history is reference/proof only
    - UserRegistered is plain object (no EventInterface)
    - listeners are invokable (no ListenerInterface)
    - multiple registrations create multiple events
    - emit uses canonical AvaX runtime

### Total Events Tests

- 84 tests across 4 test files (EventDslTest, EventFoundationTest, EventsCapabilitiesTest, EventsRuntimeClosureTest,
  EventsDogfoodingTest)
- All GREEN

### Test Coverage Assessment

- Plain event object dispatch: COVERED
- No EventInterface required: COVERED
- No ListenerInterface required: COVERED
- No-listener returns event: COVERED
- Listener invoked: COVERED
- Multiple listeners invoked: COVERED
- Priority ordering: COVERED
- Same-priority ordering: COVERED
- Listener failure bubbles: COVERED
- Stoppable event stops later listeners: COVERED
- DSL listener compiled: COVERED
- Attribute listener compiled: COVERED
- DSL + attribute share one registry: COVERED
- PSR-14 dispatch delegates to AvaX: COVERED
- PSR-14 listener provider delegates to AvaX: COVERED
- Dogfooding flow emits UserRegistered: COVERED
- Dogfooding listener invoked: COVERED
- Projection created through listener: COVERED
- Read model can be read: COVERED
- Event-history replay rebuilds projection: COVERED

## Verdict: GREEN — All required test coverage present
