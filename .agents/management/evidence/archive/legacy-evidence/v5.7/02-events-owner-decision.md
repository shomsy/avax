# V5.7 — 02 Events Canonical Owner Decision

**Date:** 2026-05-12

## Candidate Evaluation

| Candidate owner                                  | Pros                                                                                                                  | Cons                                                                                                                              | Existing code                            | Future fit                                                                                                                  | Decision                                                                                               |
|--------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------|------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------|
| `framework/System/Capabilities/Events/`          | Framework-wide visibility; user-facing API naturally belongs in framework                                             | Framework should not own reusable platform components; Events are a reusable capability, not a framework-only concern             | No existing code in framework for Events | Poor — violates component-first architecture                                                                                | REJECTED                                                                                               |
| `components/Operations/Events/`                  | Already exists; has ListenerRegistry, EventDispatcher, Events facade, Flows; priority support; tests; canonical shape | Has duplicate ListenerRegistry; no DSL yet; no PSR-14; no compiled registry; Events.php creates internals instead of accepting DI | 8 PHP files + 1 test file                | EXCELLENT — already positioned as Operations capability; can serve as general-purpose event system for all other components | **CANONICAL OWNER**                                                                                    |
| `components/Operations/MessageBus/EventBus`      | Already handles domain events; used in MessageBus context                                                             | Narrow scope (MessageBus handlers only); no priority; no stoppable events; DomainEvent marker interface forces implementation     | 3 PHP files + tests                      | POOR — MessageBus is about message routing, not general event dispatch                                                      | REJECTED as general owner. KEEP as MessageBus internal mechanism.                                      |
| `components/DataStack/Database/Telemetry/Events` | Database-specific events needed; has strategy pattern                                                                 | Very narrow scope (DB lifecycle only); childish documentation; isolated from rest of system                                       | 9 PHP files                              | POOR — database events are a consumer of general event system, not the general system itself                                | REJECTED as general owner. KEEP as database-internal telemetry. Future: emit through canonical Events. |
| `components/HTTP/Session/SessionEventBus`        | Session events needed                                                                                                 | Extremely narrow scope                                                                                                            | 2 PHP files                              | POOR — session events are a consumer, not the general system                                                                | REJECTED as general owner. KEEP as session-internal mechanism.                                         |

## Decision

**Canonical owner:** `components/Operations/Events/`

**Reason:**

- Already exists with the most complete general-purpose event dispatcher
- Already follows canonical component shape
- Already has priority support, stoppable event detection, and tests
- Positioned correctly as an Operations capability
- Can serve as the general-purpose event system that Database, Session, MessageBus, and user code all converge on

**What remains internal:**

- `Operations/MessageBus/EventBus` — stays as MessageBus internal handler dispatch. Does NOT become the general event
  system.
- `DataStack/Database/Telemetry/Events` — stays as database-internal telemetry for now. Future: may emit through
  canonical Events.
- `HTTP/Session/SessionEventBus` — stays as session-internal mechanism.
- `Operations/Events/System/Capabilities/ListenerRegistry/ListenerRegistry.php` — duplicate, superseded by
  `Registry/ListenerRegistry.php`.

**What becomes public surface:**

- `Events` facade — enhanced with DSL (`onEvent()->do()`) and `emit()` method
- `emit()` global helper function — thin wrapper
- `onEvent()` global helper function — thin DSL entry
- `#[ListensTo]` attribute — declaration-only

**What is superseded:**

- `Operations/Events/System/Capabilities/ListenerRegistry/ListenerRegistry.php` — incomplete duplicate

**What must not be duplicated:**

- No new EventBus/EventDispatcher in framework
- No new ListenerRegistry outside Operations/Events
- No new general-purpose event system in any other component
- Database, Session, and MessageBus event systems remain internal to their domains and must not be promoted as
  general-purpose
