# V5.7-01 — Owner Convergence Classification

**Date:** 2026-05-12
**Branch:** main
**Commit:** f3818bcd706058ccf4c445bc98d0c41d07be5d9f
**Scope:** Classify all event-related systems in the codebase

## Classification Table

| System | Files | Current role | Consumers | Tests | Classification | Final decision |
|---|---|---|---|---|---|---|
| `components/Operations/Events/` | 9 PHP files | General-purpose event dispatcher with priority support, stoppable events, canonical component shape | EventsCapabilitiesTest (8 tests) | EventsCapabilitiesTest — 8 tests pass | **CANONICAL_OWNER** | Keep as canonical owner. Remove duplicate ListenerRegistry. |
| `components/Operations/MessageBus/System/Capabilities/Bus/EventBus.php` | 1 PHP file (EventBus) + MessageBus suite | Simple handler registry for domain events within MessageBus context (CommandBus, QueryBus, EventBus) | PublishEvent flow, MessageBus tests, ConsumerTest, MessagingIntegrationTest | EventBusTest, MessageBusCapabilitiesTest | **MESSAGEBUS_SPECIFIC_ADAPTER** | Keep as MessageBus-internal mechanism. Does NOT claim generic event dispatch ownership. |
| `components/DataStack/Database/System/Capabilities/Telemetry/Events/` | 11 PHP files | Database-specific lifecycle event bus with strategy pattern (ConnectionAcquired, QueryExecuted, etc.) | DatabaseBuilder, DatabaseLoggerSubscriber | No direct tests | **DATABASE_TELEMETRY_EVENT_SOURCE** | Keep as database-internal telemetry. Does NOT claim generic event dispatch ownership. |
| `components/HTTP/Session/System/Capabilities/Events/SessionEventBus.php` + `SessionEvent.php` | 2 PHP files | Session-lifecycle event bus with once/listen/dispatch/removeListener | Session component internals | No direct tests | **SESSION_LIFECYCLE_EVENT_SOURCE** | Keep as session-internal mechanism. Does NOT claim generic event dispatch ownership. |
| `components/DeveloperTools/Testing/System/Capabilities/Fakes/EventFake.php` | 1 PHP file | Test fake for Operations/Events-style dispatch assertions | Test infrastructure | TestingCapabilitiesTest | **TEST_FIXTURE** | Keep. Future: converge API with framework EventFake. |
| `framework/System/Capabilities/TestingFakes/EventFake.php` | 1 PHP file | Test fake for framework-level event dispatch with payload recording | Test infrastructure | No direct test file | **TEST_FIXTURE** | Keep. Future: converge API with DeveloperTools EventFake. |
| `components/Operations/Events/System/Capabilities/ListenerRegistry/ListenerRegistry.php` | 1 PHP file | Incomplete duplicate of Registry/ListenerRegistry (missing remove, hasListeners, listenerCount) | Referenced by old code but NOT used by Events.php (which uses Registry/ListenerRegistry) | No direct tests | **LEGACY_DUPLICATE_TO_SUPERSEDE** | Remove. Superseded by `Registry/ListenerRegistry.php`. |

## Code Evidence

### Operations/Events (CANONICAL_OWNER)

Files inspected:
- `System/PublicSurface/Events.php` — Creates own ListenerRegistry + EventDispatcher, exposes dispatch/listen/flush/forget/hasListeners/listenerCount
- `System/PublicSurface/EventsInterface.php` — Interface for Events facade
- `System/Capabilities/Registry/ListenerRegistry.php` — Complete registry with subscribe, remove, hasListeners, listenerCount, getListenersFor, clear, priority sorting
- `System/Capabilities/Dispatcher/EventDispatcher.php` — Dispatches with stoppable event support (duck-typed isPropagationStopped)
- `System/Flows/DispatchEvent/DispatchEvent.php` — Thin flow wrapper
- `System/Flows/SubscribeToEvent/SubscribeToEvent.php` — Thin flow wrapper
- `System/Configuration/RegisterEventDependencies.php` — Static registration
- `System/Foundation/Failure/EventsFailure.php` — Failure exception
- `System/Capabilities/ListenerRegistry/ListenerRegistry.php` — **INCOMPLETE DUPLICATE** (only subscribe, getListenersFor, clear)

Key finding: Events.php imports `Registry\ListenerRegistry`, NOT `ListenerRegistry\ListenerRegistry`. The duplicate is dead code.

### MessageBus/EventBus (MESSAGEBUS_SPECIFIC_ADAPTER)

Files inspected:
- `System/Capabilities/Bus/EventBus.php` — Simple register(eventClass, handler) / dispatch(event) / handlersFor(eventClass)
- Part of broader MessageBus component with CommandBus, QueryBus, middleware, outbox, inbox, consumer, projection

Scope is clearly MessageBus-internal. No priority support. No stoppable events. No PSR-14.

### Database/Telemetry/Events (DATABASE_TELEMETRY_EVENT_SOURCE)

Files inspected:
- `System/Capabilities/Telemetry/Events/EventBus.php` — Database-specific with DispatchStrategyInterface
- `System/Capabilities/Telemetry/Events/Event.php` — Base database event with getName()
- `System/Capabilities/Telemetry/Events/EventBusInterface.php` — Contract for database event bus
- `System/Capabilities/Telemetry/Events/EventSubscriberInterface.php` — Subscriber contract
- `System/Capabilities/Telemetry/Events/Strategy/SyncDispatchStrategy.php` — Sync dispatch strategy
- `System/Capabilities/Telemetry/Events/Subscribers/DatabaseLoggerSubscriber.php` — Logs database events
- `System/Capabilities/Telemetry/Events/{ConnectionAcquired,ConnectionFailed,ConnectionOpened,QueryExecuted}.php` — Specific events

Narrow scope: database lifecycle telemetry only. Does not compete with general-purpose events.

### Session/SessionEventBus (SESSION_LIFECYCLE_EVENT_SOURCE)

Files inspected:
- `System/Capabilities/Events/SessionEventBus.php` — once/listen/dispatch/removeListener for session events
- `System/Foundation/SessionEvent.php` — Session event wrapper

Extremely narrow scope: session lifecycle only. Does not compete with general-purpose events.

## Conclusion

Only one system claims general-purpose event dispatch ownership: `components/Operations/Events/`.
The other three are domain-scoped adapters that do not contradict this ownership.
One internal duplicate (`ListenerRegistry/ListenerRegistry.php`) must be removed to eliminate confusion.
