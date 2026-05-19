# V5.7 — 09 Future Compatibility Design

**Date:** 2026-05-12

## How V5.7 Enables Future Phases Without Implementing Them

V5.7 designs APIs with extension points that future phases will use. These extension points are documented here but NOT
implemented in V5.7.

---

## V5.8 — Database Lifecycle Events

**ROADMAP — NOT IMPLEMENTED IN V5.7**

### Future API

```php
// Entity lifecycle
onEntity(User::class)->beforeSave(ValidateUserData::class);
onEntity(User::class)->afterCreate(SendWelcomeEmail::class);
onEntity(User::class)->afterUpdate(LogUserChanges::class);
onEntity(User::class)->beforeDelete(CheckUserDependencies::class);

// Transaction lifecycle
onTransaction()->afterCommit(PublishToOutbox::class);
onTransaction()->afterRollback(CleanupTemporaryFiles::class);
```

### How V5.7 Enables This

- `onEvent()` DSL provides the registration pattern that `onEntity()` will extend
- `#[ListensTo]` attribute will gain an `afterCommit: true` parameter
- Compiled listener registry will support execution modes (sync, after_commit)
- Event model (plain readonly objects) fits database lifecycle events naturally

### What V5.7 Does NOT Do

- No `onEntity()` DSL
- No `onTransaction()` DSL
- No database lifecycle event classes
- No transaction hook integration

---

## V5.9 — Fluent Boot DSL & Boot Lifecycle

**ROADMAP — NOT IMPLEMENTED IN V5.7**

### Future API

```php
avax()
    ->from(__DIR__)
    ->config('config/*.php')
    ->routes('routes/*.php')
    ->events('events.php')   // Loads event DSL registrations
    ->boot();
```

### How V5.7 Enables This

- `GlobalEventRegistry` provides a boot-time coordination point
- `CompileEventListeners` flow provides a compilation step the boot pipeline can call
- `CompiledListenerRegistry::freeze()` provides immutability guarantee after boot
- Global helper functions (`onEvent()`, `emit()`) are boot-compatible

### What V5.7 Does NOT Do

- No `avax()->events()` boot method
- No event file loading/compilation during boot
- No boot report/doctor for events

---

## V5.10 — FailureBoundary Integration

**ROADMAP — NOT IMPLEMENTED IN V5.7**

### Future API

```php
#[OnFailure(ReportFailure::class)]
#[OnFailure(Retry::class, maxAttempts: 3)]
onEvent(UserRegistered::class)
    ->do(SendWelcomeEmail::class);
```

### How V5.7 Enables This

- Events are dispatched through clean boundaries
- Listener invocation is a clear execution point where FailureBoundary can wrap
- FailureBoundary attributes already exist and compile at boot time

### What V5.7 Does NOT Do

- No FailureBoundary integration with event dispatch
- No listener retry/fallback/dead-letter

---

## V5.10+ — Outbox Pattern

**ROADMAP — NOT IMPLEMENTED IN V5.7**

### Future API

```php
onEvent(UserRegistered::class)
    ->do(PublishToOutbox::class, afterCommit: true);
```

### How V5.7 Enables This

- `afterCommit` execution mode reserved in `ListenerExecutionMode` enum
- `#[ListensTo]` attribute has `afterCommit` parameter reserved

---

## V6.8 — Typed Scenario DSL

**ROADMAP — NOT IMPLEMENTED IN V5.7**

### Future API

```php
// Assertion DSL for testing
event(UserRegistered::class)->wasEmitted();
event(UserRegistered::class)->wasEmitted(1);
event(UserRegistered::class)->wasNotEmitted();
event(OrderPaid::class)->emittedBefore(InvoiceIssued::class);
```

### How V5.7 Enables This

- Clean event emission through `emit()` function
- Event types are plain class-strings, easy to assert against
- `EventFake` test fakes already track dispatched events

---

## Rules for Future APIs

1. Future APIs must NOT be promised as active in documentation.
2. They must be marked ROADMAP.
3. No placeholder production code for future phases.
4. Extension points (enums, reserved parameters) are documented but not functional.
5. Users cannot accidentally use future APIs — they do not exist yet.
