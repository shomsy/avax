# V5.7-51: Event Behavior Final Audit

**Date:** 2026-05-13
**Branch:** main

## Event Behavior Audit

### One Canonical Events Owner

- `components/Operations/Events/` — confirmed as canonical owner
- check-canonical-event-owner.php PASS — no competing generic owner

### onEvent()->do() Registration

- DSL works: `onEvent(EventClass::class)->do(Listener::class, priority: N)`
- Returns same DSL for chaining
- Registers into canonical ListenerRegistry
- gate: check-fluent-dsl-registration.php PASS

### emit(new Event(...)) Dispatch

- `emit(object $event): object` global function
- Delegates to EventEmitter
- Returns same event object
- gate: check-event-emission-api.php PASS

### #[ListensTo] Compile Path

- Attribute declaration compiles at boot time
- Runtime dispatch does not scan attributes
- gate: check-listens-to-attribute.php PASS

### Compiled Registry Freeze/Read

- CompiledListenerRegistry freezes after compilation
- No further registrations after freeze
- gate: check-compiled-listener-registry.php PASS

### Priority Ordering

- Higher priority executes first (descending sort)
- Same priority preserves registration order (stable sort)
- Tests prove both behaviors — GREEN

### No-Listener Behavior

- emit() returns event unchanged when no listeners registered
- Tests prove — GREEN

### Listener Failure Bubbling

- Listener exceptions bubble by default
- Tests prove — GREEN

### Stoppable Event Behavior

- Duck-typed isPropagationStopped() check
- Stops later listeners when true
- Tests prove — GREEN

### PSR-14 Adapter

- Psr14EventDispatcherAdapter dispatches via AvaX EventEmitter
- Psr14ListenerProviderAdapter reads from CompiledListenerRegistry
- PSR StoppableEventInterface respected via duck-typing
- gate: check-psr14-interop.php PASS

### Real Dogfooding

- SecureRegistrationApi emits UserRegistered
- 3 listeners invoked: audit, projection, event-history
- RegisteredUserView built and queryable
- Event replay rebuilds projection
- gate: check-real-dogfooding.php PASS

### CQRS Projection Proof

- Write side → event → projector → read model → query
- No generic CQRS folder
- Tests prove — GREEN

### Event-History Replay Proof

- ReferenceEventHistoryStore stores events
- ReplayEventHistory rebuilds projection
- Marked reference/proof only
- Tests prove — GREEN

### No EventInterface / No ListenerInterface Requirement

- User events are plain objects
- User listeners are plain invokable classes
- Tests prove — GREEN

### No Runtime Attribute Reflection

- Hot path (EventEmitter::emit) has no reflection
- gate: check-events-no-hot-path-reflection.php PASS

### No class-string/array/DTO Emission as Production API

- emit() only accepts object
- gate: check-event-emission-api.php PASS

### No Queued/Async/DB Lifecycle/Event Sourcing Production Claim

- All marked as ROADMAP
- gate: check-event-sourcing-not-default.php PASS

## Verdict: GREEN

All behavioral properties verified through gates, tests, and code inspection.
