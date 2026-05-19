# V5.7 — 01 Existing Event System Audit

**Date:** 2026-05-12
**Scope:** All event-related code in framework, components, tests, docs, EVIDENCE

## Search Pattern

```
EventBus|EventDispatcher|ListenerRegistry|ListenerProvider|DomainEvent|EventSubscriber|
dispatch(.*event)|emit(|Listener|Subscriber|StoppableEvent|Psr\\EventDispatcher
```

## Detailed Inventory

### 1. Operations/Events — General-Purpose Event Dispatcher

| Area              | File(s)                                                     | Concept               | Current Responsibility                                                                                                                  | Used By                                                                           | Tests                  | Classification      |
|-------------------|-------------------------------------------------------------|-----------------------|-----------------------------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------|------------------------|---------------------|
| Operations/Events | `System/Capabilities/ListenerRegistry/ListenerRegistry.php` | ListenerRegistry (v1) | Incomplete registry — subscribe, getListenersFor, clear only                                                                            | Referenced by Events.php but NOT used (Events.php uses Registry/ListenerRegistry) | No direct tests        | SUPERSEDED          |
| Operations/Events | `System/Capabilities/Registry/ListenerRegistry.php`         | ListenerRegistry (v2) | Complete registry — subscribe, remove, hasListeners, listenerCount, getListenersFor, clear                                              | Events.php PublicSurface                                                          | EventsCapabilitiesTest | CANONICAL_CANDIDATE |
| Operations/Events | `System/Capabilities/Dispatcher/EventDispatcher.php`        | EventDispatcher       | Dispatches events to listeners from registry, supports stoppable events via duck-typed isPropagationStopped                             | Events.php PublicSurface                                                          | EventsCapabilitiesTest | CANONICAL_CANDIDATE |
| Operations/Events | `System/Flows/DispatchEvent/DispatchEvent.php`              | Flow                  | Wraps EventsInterface::dispatch                                                                                                         | Not directly used in production flows                                             | No direct flow tests   | INTERNAL_MECHANISM  |
| Operations/Events | `System/Flows/SubscribeToEvent/SubscribeToEvent.php`        | Flow                  | Wraps ListenerRegistry::subscribe                                                                                                       | Not directly used in production flows                                             | No direct flow tests   | INTERNAL_MECHANISM  |
| Operations/Events | `System/PublicSurface/Events.php`                           | PublicSurface         | Facade — creates its own ListenerRegistry + EventDispatcher internally, exposes dispatch/listen/flush/forget/hasListeners/listenerCount | Not used by other components                                                      | EventsCapabilitiesTest | CANONICAL_CANDIDATE |
| Operations/Events | `System/PublicSurface/EventsInterface.php`                  | Contract              | Interface for Events facade                                                                                                             | DispatchEvent flow                                                                | —                      | CANONICAL_CANDIDATE |
| Operations/Events | `System/Configuration/RegisterEventDependencies.php`        | Configuration         | Static registration — Events::setDispatcher                                                                                             | Not used in current boot                                                          | —                      | INTERNAL_MECHANISM  |

**Issues found:**

- **DUPLICATE OWNER**: Two `ListenerRegistry` classes exist in different sub-namespaces.
  `ListenerRegistry/ListenerRegistry.php` is incomplete (missing remove, hasListeners, listenerCount).
  `Registry/ListenerRegistry.php` is the complete one actually used by Events.php.
- **No DSL**: Registration is imperative (`$events->listen(...)`) not declarative (`onEvent()->do()`).
- **No emit()**: Uses `dispatch()` naming, not `emit()`.
- **No attribute support**: No `#[ListensTo]` attribute.
- **No compiled registry**: All registration is runtime-only.
- **No PSR-14**: No PSR-14 adapter or interface implementation.
- **String+object events**: Supports both string event names and object events, but not cleanly typed.
- **Events.php creates its own internals**: Does not accept injected dependencies, making it hard to wire into a
  container.

### 2. Operations/MessageBus/EventBus — Domain Event Bus

| Area                  | File(s)                                                            | Concept          | Current Responsibility                                                                | Used By                                            | Tests                                                                            | Classification     |
|-----------------------|--------------------------------------------------------------------|------------------|---------------------------------------------------------------------------------------|----------------------------------------------------|----------------------------------------------------------------------------------|--------------------|
| Operations/MessageBus | `System/Capabilities/Bus/EventBus.php`                             | EventBus         | Simple handler registry — register(eventClass, handler), dispatch(event), handlersFor | MessageBus PublicSurface, PublishEvent flow, tests | EventBusTest, MessageBusCapabilitiesTest, ConsumerTest, MessagingIntegrationTest | INTERNAL_MECHANISM |
| Operations/MessageBus | `System/PublicSurface/DomainEvent.php`                             | Marker interface | Empty marker interface for domain events                                              | EventBusTest, MessageBus tests                     | —                                                                                | DOMAIN_EVENT_ONLY  |
| Operations/MessageBus | `System/Flows/PublishEvent/PublishEvent.php`                       | Flow             | Thin wrapper around EventBus::dispatch                                                | —                                                  | —                                                                                | INTERNAL_MECHANISM |
| Operations/MessageBus | `System/Flows/DispatchTransactionally/DispatchTransactionally.php` | Flow             | Dispatches events after transaction commit                                            | —                                                  | —                                                                                | INTERNAL_MECHANISM |
| Operations/MessageBus | `System/PublicSurface/MessageBus.php`                              | PublicSurface    | Unified bus (CommandBus, QueryBus, EventBus)                                          | Tests                                              | MessageBusTest                                                                   | INTERNAL_MECHANISM |

**Issues found:**

- EventBus is a **subset** of MessageBus, not a general-purpose event system.
- No priority support.
- No stoppable events.
- No PSR-14.
- DomainEvent is a marker interface — contradicts "no forced interfaces" principle.

### 3. DataStack/Database/Telemetry/Events — Database Lifecycle Events

| Area               | File(s)                                                                                                         | Concept       | Current Responsibility                                     | Used By                                   | Tests | Classification      |
|--------------------|-----------------------------------------------------------------------------------------------------------------|---------------|------------------------------------------------------------|-------------------------------------------|-------|---------------------|
| DataStack/Database | `System/Capabilities/Telemetry/Events/EventBus.php`                                                             | EventBus      | Database-specific event bus with dispatch strategy pattern | DatabaseBuilder, DatabaseLoggerSubscriber | —     | DATABASE_EVENT_ONLY |
| DataStack/Database | `System/Capabilities/Telemetry/Events/Event.php`                                                                | Domain event  | Database event with getName() method                       | All database events                       | —     | DATABASE_EVENT_ONLY |
| DataStack/Database | `System/Capabilities/Telemetry/Events/EventBusInterface.php`                                                    | Contract      | Interface for database event bus                           | EventBus                                  | —     | DATABASE_EVENT_ONLY |
| DataStack/Database | `System/Capabilities/Telemetry/Events/EventSubscriberInterface.php`                                             | Contract      | Subscriber interface                                       | DatabaseLoggerSubscriber                  | —     | DATABASE_EVENT_ONLY |
| DataStack/Database | `System/Capabilities/Telemetry/Events/Strategy/SyncDispatchStrategy.php`                                        | Strategy      | Sync dispatch strategy                                     | EventBus constructor                      | —     | DATABASE_EVENT_ONLY |
| DataStack/Database | `System/Capabilities/Telemetry/Events/Subscribers/DatabaseLoggerSubscriber.php`                                 | Subscriber    | Logs database events                                       | EventBus                                  | —     | DATABASE_EVENT_ONLY |
| DataStack/Database | `System/Capabilities/Telemetry/Events/{ConnectionAcquired,ConnectionFailed,ConnectionOpened,QueryExecuted}.php` | Domain events | Specific database events                                   | EventBus                                  | —     | DATABASE_EVENT_ONLY |

**Issues found:**

- Database-specific event system with its own EventBus, Event, EventBusInterface, EventSubscriberInterface.
- Does NOT integrate with Operations/Events.
- Does NOT integrate with Operations/MessageBus/EventBus.
- Uses childish naming in comments ("Radio Command Center", "Shout") — not production-ready documentation quality.

### 4. HTTP/Session/Events — Session Event Bus

| Area         | File(s)                                          | Concept         | Current Responsibility                                              | Used By                     | Tests | Classification     |
|--------------|--------------------------------------------------|-----------------|---------------------------------------------------------------------|-----------------------------|-------|--------------------|
| HTTP/Session | `System/Capabilities/Events/SessionEventBus.php` | SessionEventBus | Session-specific event bus with once/listen/dispatch/removeListener | Session component internals | —     | INTERNAL_MECHANISM |
| HTTP/Session | `System/Foundation/SessionEvent.php`             | Domain event    | Session event wrapper                                               | SessionEventBus             | —     | INTERNAL_MECHANISM |

**Issues found:**

- Third independent event system.
- No integration with Operations/Events or MessageBus.
- Very narrow scope (session lifecycle only).

### 5. Testing Fakes

| Area                   | File(s)                                          | Concept   | Current Responsibility                                               | Used By             | Tests                   | Classification |
|------------------------|--------------------------------------------------|-----------|----------------------------------------------------------------------|---------------------|-------------------------|----------------|
| DeveloperTools/Testing | `System/Capabilities/Fakes/EventFake.php`        | Test fake | Captures dispatched events, provides assertions                      | Test infrastructure | TestingCapabilitiesTest | TEST_FIXTURE   |
| Framework              | `System/Capabilities/TestingFakes/EventFake.php` | Test fake | Records dispatched events with payloads, prevent/allow real dispatch | Test infrastructure | —                       | TEST_FIXTURE   |

**Issues found:**

- **DUPLICATE**: Two EventFake implementations with different APIs.
- DeveloperTools version uses `dispatch(string|object $event, mixed $data)` — matches Operations/Events.
- Framework version uses `dispatch(string $event, array $payload)` — different API.

## Summary

| Classification      | Count   | Systems                                                                           |
|---------------------|---------|-----------------------------------------------------------------------------------|
| CANONICAL_CANDIDATE | 5 files | Operations/Events (ListenerRegistry v2, EventDispatcher, Events, EventsInterface) |
| INTERNAL_MECHANISM  | 6 files | Operations/Events flows, MessageBus EventBus, SessionEventBus                     |
| DOMAIN_EVENT_ONLY   | 1 file  | MessageBus/DomainEvent marker interface                                           |
| DATABASE_EVENT_ONLY | 9 files | DataStack/Database telemetry events                                               |
| TEST_FIXTURE        | 2 files | DeveloperTools/Testing EventFake, Framework EventFake                             |
| LEGACY_DUPLICATE    | 1 file  | Operations/Events/ListenerRegistry/ListenerRegistry.php (incomplete duplicate)    |
| SUPERSEDED          | 0       | —                                                                                 |

## Key Finding: DUPLICATE EVENT OWNERS

There are **4 independent event dispatchers** in the codebase:

1. `Operations/Events` — General-purpose, closest to canonical candidate
2. `Operations/MessageBus/EventBus` — MessageBus-level, domain event handlers
3. `DataStack/Database/Telemetry/Events` — Database lifecycle only
4. `HTTP/Session/SessionEventBus` — Session lifecycle only

Only `Operations/Events` has:

- Priority support
- Sorted listener cache
- Stoppable event support (duck-typed)
- Canonical component shape
- Tests

`Operations/Events` is the most complete and closest to canonical ownership for general-purpose events.
