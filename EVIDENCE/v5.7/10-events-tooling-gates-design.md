# V5.7 — 10 Events Tooling & Gates Design

**Date:** 2026-05-12

## Proposed Gates

### 1. check-events-canonical-owner.php

**Purpose:** Ensure exactly one canonical event system owner.

**Fail if:**

- Multiple EventBus/EventDispatcher owners exist at framework level
- `Operations/Events` does not exist or is empty
- A new general-purpose event system is created outside `Operations/Events`

**Pass if:**

- Only `Operations/Events` contains general-purpose event dispatcher
- Internal event systems (Database, Session, MessageBus) are confined to their component

---

### 2. check-events-no-hot-path-reflection.php

**Purpose:** Ensure no reflection in the event dispatch hot path.

**Fail if:**

- `ReflectionClass` or `ReflectionAttribute` used in `EventDispatcher`, `EventEmitter`, or `InvokeEventListener`
- Attribute scanning happens during `emit()` call

**Pass if:**

- Reflection only used in compile/build phase
- Runtime dispatch uses pre-compiled registry

---

### 3. check-events-attributes-compiled.php

**Purpose:** Ensure `#[ListensTo]` attributes result in actual compiled listeners.

**Fail if:**

- `#[ListensTo]` attributes exist but compiled listener registry does not include them
- Attribute-discovered listeners are not invoked during dispatch

**Pass if:**

- All `#[ListensTo]` attributes are accounted for in compiled registry
- Tests prove attribute listeners are dispatched

---

### 4. check-events-dsl-adoption.php

**Purpose:** Ensure `onEvent()->do()` DSL and `emit()` are the primary user-facing APIs.

**Fail if:**

- DSL registers listeners but dispatcher ignores the registry
- `emit()` does not use the compiled listener registry

**Pass if:**

- `onEvent()->do()` successfully registers listeners
- `emit()` dispatches through compiled registry

---

### 5. check-psr14-interop.php

**Purpose:** Ensure PSR-14 adapter is correct when PSR-14 is installed.

**Fail if:**

- `psr/event-dispatcher` is installed but AvaX PSR-14 adapter does not exist
- Docs claim PSR-14 compatibility but adapter tests fail

**Pass if:**

- When PSR-14 is installed, adapter exists and passes tests
- When PSR-14 is not installed, AvaX works without error
- `dispatch($event)` returns event per PSR-14 spec

---

### 6. check-events-no-forced-interfaces.php

**Purpose:** Ensure user events and listeners are not forced into unnecessary interfaces.

**Fail if:**

- Event classes are required to implement `EventInterface` marker interface
- Listener classes are required to implement `ListenerInterface` marker interface
- `DomainEvent` interface is enforced on all user events

**Pass if:**

- Plain readonly event objects work without interfaces
- Invokable listener classes work without interfaces
- PSR-14 `StoppableEventInterface` is optional (only for stoppable events)

---

## Gate Implementation Priority

| Gate                                    | Priority | Stage   |
|-----------------------------------------|----------|---------|
| check-events-canonical-owner.php        | HIGH     | V5.7-10 |
| check-events-no-hot-path-reflection.php | HIGH     | V5.7-10 |
| check-events-attributes-compiled.php    | HIGH     | V5.7-10 |
| check-events-dsl-adoption.php           | MEDIUM   | V5.7-10 |
| check-psr14-interop.php                 | MEDIUM   | V5.7-10 |
| check-events-no-forced-interfaces.php   | LOW      | V5.7-10 |

**Note:** Gates are DESIGNED in this pass. Implementation happens in V5.7-10 stage.
