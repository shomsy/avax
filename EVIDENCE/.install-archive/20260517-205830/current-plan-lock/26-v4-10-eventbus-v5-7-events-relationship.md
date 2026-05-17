# 26 — V4-10 EventBus vs V5.7 Events Relationship

**Date:** 2026-05-12
**Branch:** main
**Commit:** 21d6f69fd
**Scope:** Verify V4-10 EventBus relationship to V5.7 Events DSL

## V4-10 Messaging & Consistency — EventBus

**File:** `components/Operations/MessageBus/System/Capabilities/Bus/EventBus.php`

**Implementation:** Simple in-memory event bus with handler registration and synchronous dispatch.

```php
final class EventBus {
    private array $handlers = [];
    public function register(string $eventClass, object $handler): void
    public function dispatch(object $event): void
    public function handlersFor(string $eventClass): array
}
```

**Scope:** Part of the MessageBus component (CommandBus, QueryBus, EventBus). It is a specific adapter for event
dispatch within the MessageBus component's domain.

**Other EventBus instances:**

- `components/DataStack/Database/System/Capabilities/Telemetry/Events/EventBus.php` — Database-specific event bus
- `components/HTTP/Session/System/Capabilities/Events/SessionEventBus.php` — Session-specific event bus

These are domain-specific event buses, not the canonical system-wide event system.

## V5.7 Events DSL — Canonical Owner

**Directory:** `components/Operations/Events/`

**Implementation:** Not yet implemented. Design lock GREEN.

**Design:**

- DSL: `onEvent(Event::class)->do(Listener::class)`
- Dispatch: `emit(new Event())`
- Attribute: `#[ListensTo(Event::class)]`
- Compiled registry: in-memory for V5.7
- Events are plain readonly objects (no interface required)
- Listeners are invokable classes (no interface required)
- PSR-14: optional adapter

**Canonical owner decision:** `components/Operations/Events/`

## Relationship Analysis

### Does V4-10 contradict V5.7 canonical owner decision?

**NO.** V4-10 EventBus is a **MessageBus-specific adapter** within the MessageBus component. It is one of several
domain-specific event buses in the codebase (Database EventBus, Session EventBus). It serves the MessageBus component's
internal event dispatch needs.

V5.7 Events is the **canonical system-wide event system** with:

- Fluent DSL
- Compiled listener registry
- `#[ListensTo]` attribute
- PSR-14 optional adapter
- Cross-component event ownership

### Does V5.7-01 Owner Convergence need to explicitly handle V4-10?

**YES, but as a migration note, not a blocker.** V5.7-01 (Canonical Owner Convergence) should note that:

1. `components/Operations/MessageBus/System/Capabilities/Bus/EventBus.php` exists as a simpler event bus
2. `components/Operations/Events/` is the canonical system-wide event system
3. MessageBus EventBus may continue to exist as a MessageBus-specific adapter, or may be converged into the canonical
   Events component later
4. Database EventBus and Session EventBus are domain-specific and may remain or be converged separately

This is a **convergence decision**, not a **contradiction**. Multiple event dispatch mechanisms serving different scopes
is an acceptable architecture (similar to how multiple HTTP clients exist for different purposes).

### Does this block V5.7-01?

**NO.** V5.7-01 (Owner Convergence) is about establishing `components/Operations/Events/` as the canonical owner. The
existing EventBus instances are domain-specific adapters, not competing canonical systems. V5.7-01 can proceed without
resolving MessageBus EventBus convergence.

## Classification: MESSAGEBUS_SPECIFIC_ADAPTER

V4-10 EventBus is a **MessageBus-specific adapter**, not a competing canonical event system.

| Aspect            | V4-10 EventBus           | V5.7 Events                   |
|-------------------|--------------------------|-------------------------------|
| Owner             | MessageBus component     | Operations/Events (canonical) |
| Scope             | MessageBus events only   | System-wide events            |
| DSL               | None (register/dispatch) | `onEvent()->do()`             |
| Attributes        | None                     | `#[ListensTo]`                |
| Compiled registry | No                       | Yes                           |
| PSR-14            | No                       | Optional adapter              |
| Status            | GREEN (existing)         | NOT_STARTED (design locked)   |

## Remaining Risks

- Event system duplication exists (4 EventBus instances + future V5.7 Events). Convergence is a ROADMAP item.
- V5.7 implementation should consider whether to integrate with or replace existing EventBus instances.
- This is a design decision for V5.7-01 Owner Convergence, not a blocker.

## Next Allowed Action

Apply safe fixes (mark stale evidence as SUPERSEDED, fix stale docs).
