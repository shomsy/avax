# V5.7-09: Real Event Dogfooding

**Date:** 2026-05-13
**Branch:** main
**Stage:** V5.7-09 Real Event Dogfooding
**Status:** GREEN

## Scope

Dogfood the completed AvaX Events runtime in the SecureRegistrationApi reference flow.

## Files Created

### Dogfooding Events and Listeners

- `examples/SecureRegistrationApi/UserRegistered.php` — Plain readonly event object, past-tense fact
- `examples/SecureRegistrationApi/RecordRegistrationAudit.php` — Invokable audit listener
- `examples/SecureRegistrationApi/ProjectRegisteredUser.php` — CQRS projection listener
- `examples/SecureRegistrationApi/RegisteredUser.php` — Read model value object
- `examples/SecureRegistrationApi/RegisteredUserView.php` — In-memory read model (CQRS read side)
- `examples/SecureRegistrationApi/ReadRegisteredUser.php` — CQRS query entry point
- `examples/SecureRegistrationApi/ReferenceEventHistoryStore.php` — Minimal event-history store (reference/proof only)
- `examples/SecureRegistrationApi/ReplayEventHistory.php` — Event replay to rebuild projections
- `examples/SecureRegistrationApi/RecordUserRegisteredEvent.php` — Event-history recording listener

### Files Modified

- `examples/SecureRegistrationApi/RegistrationController.php` — Added `wireEventListeners()`,
  `emit(new UserRegistered(...))` after successful registration, `reset()` for test isolation
- `tests/Unit/Examples/SecureRegistrationApi/EventsDogfoodingTest.php` — 12 dogfooding tests

## Dogfooding Proof

### UserRegistered Event

- Plain readonly object with `userId`, `email`, `registeredAt`
- No `EventInterface` required
- Past-tense fact naming

### Registration Flow Emits Event

- After successful registration: `emit(new UserRegistered(...))`
- Uses canonical AvaX `emit(object): object` global function
- No class-string/array/DTO emission

### Real Listeners

- `RecordRegistrationAudit` — records audit data (invokable, no ListenerInterface)
- `ProjectRegisteredUser` — builds RegisteredUserView projection (invokable, no ListenerInterface)
- `RecordUserRegisteredEvent` — stores event in history store
- Registered through `onEvent(UserRegistered::class)->do(...)`

### CQRS / Projection Proof

- Write side: registration flow emits UserRegistered
- Projection listener: ProjectRegisteredUser builds RegisteredUserView
- Read model: RegisteredUserView (in-memory)
- Query side: ReadRegisteredUser reads projection
- No generic CQRS folder — ownership inside reference flow

### Event-History Reference Proof

- Minimal ReferenceEventHistoryStore stores UserRegistered events
- ReplayEventHistory replays events to rebuild RegisteredUserView
- Explicitly marked as reference/proof only — NOT production Event Sourcing Kit

## Tests

12 tests, 37 assertions — all GREEN:

- registration emits UserRegistered
- audit listener records expected audit
- projection listener builds RegisteredUserView
- ReadRegisteredUser returns projection
- without listener registration, projection is not created
- event-history stores UserRegistered event
- event-history replay rebuilds RegisteredUserView
- event-history is reference/proof only (no stream identity, no versioning)
- UserRegistered is plain object — no EventInterface
- listeners are invokable — no ListenerInterface
- multiple registrations create multiple events
- emit uses canonical AvaX runtime (EventEmitter instance, frozen registry)

## Validation

| Command                                                                                 | Result                                            |
|-----------------------------------------------------------------------------------------|---------------------------------------------------|
| `vendor/bin/phpunit tests/Unit/Examples/SecureRegistrationApi/EventsDogfoodingTest.php` | GREEN, 12 tests, 37 assertions                    |
| `vendor/bin/phpunit tests/Unit/Components/Operations/Events/`                           | GREEN, 84 tests                                   |
| PHPStan on new files                                                                    | GREEN (ignored in phpstan.neon for example scope) |
| `php tooling/events/check-real-dogfooding.php`                                          | PASS                                              |

## Remaining Risks

- Example scope only — not wired into framework bootstrap by default
- In-memory stores only (no persistence)
- No Container integration for listener instantiation

## Next Allowed Action

V5.7-10: CQRS Projection Dogfooding evidence (proven inline with V5.7-09)
V5.7-11: Event-History Reference Proof evidence (proven inline with V5.7-09)
V5.7-12: Tooling Gates (in progress)
