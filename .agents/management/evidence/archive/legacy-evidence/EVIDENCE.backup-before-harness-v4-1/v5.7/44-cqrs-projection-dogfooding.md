# V5.7-10: CQRS Projection Dogfooding

**Date:** 2026-05-13
**Branch:** main
**Stage:** V5.7-10 CQRS / Projection Dogfooding
**Status:** GREEN

## Scope

Prove that the AvaX Events runtime can drive a CQRS projection in a real reference flow.

## Proof

### Write Side

- Registration flow emits `UserRegistered(userId, email, registeredAt)` event
- Event is dispatched through canonical AvaX EventEmitter

### Projection Listener

- `ProjectRegisteredUser` is an invokable listener (no ListenerInterface)
- Registered through `onEvent(UserRegistered::class)->do(ProjectRegisteredUser::class)`
- On invocation, builds `RegisteredUser` and stores in `RegisteredUserView`

### Read Model

- `RegisteredUserView` — in-memory read model
- `RegisteredUser` — value object with userId, email, registeredAt
- `ReadRegisteredUser` — query entry point: `byUserId()`, `all()`

### Verification

- Test: `projection_listener_builds_registered_user_view` — GREEN
- Test: `read_registered_user_returns_projection` — GREEN
- Test: `without_listener_registration_projection_is_not_created` — GREEN
- Test: `multiple_registrations_create_multiple_events` — 3 users, 3 projections — GREEN

## Architecture

No generic CQRS folder. All ownership stays inside `examples/SecureRegistrationApi/`:

- Event → Capability (ProjectRegisteredUser)
- Read model → Foundation (RegisteredUserView, RegisteredUser)
- Query → Capability (ReadRegisteredUser)

This is reference/proof only. NOT production CQRS infrastructure.

## Validation

| Command                                                    | Result                                |
|------------------------------------------------------------|---------------------------------------|
| `php tooling/events/check-real-dogfooding.php`             | PASS (27 checks including CQRS proof) |
| PHPUnit CQRS tests                                         | GREEN                                 |
| `php tooling/refactor/check-component-suite-structure.php` | PASS                                  |

## Next Allowed Action

V5.7-11: Event-History Reference Proof
