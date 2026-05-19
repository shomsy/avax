# V5.7-11: Event-History Reference Proof

**Date:** 2026-05-13
**Branch:** main
**Stage:** V5.7-11 Event-History Reference Proof
**Status:** GREEN_REFERENCE_ONLY

## Scope

Prove that stored events can be replayed to rebuild projections.

## IMPORTANT DISCLAIMER

This is reference/proof only. NOT production Event Sourcing Kit.

Production Event Sourcing requires:

- EventStore with persistence
- Stream identity
- Stream versioning
- Optimistic concurrency
- Serializer
- Upcasting
- Snapshots
- Projection runner
- Replay engine

None of these are implemented or claimed.

## Proof Components

### ReferenceEventHistoryStore

- In-memory event store
- `append(object $event)` — stores event
- `eventsOf(string $eventClass)` — returns events for replay
- `all()` — returns all events
- `reset()` — test isolation

### ReplayEventHistory

- `replay(array $events)` — iterates events, invokes ProjectRegisteredUser for UserRegistered events
- Rebuilds RegisteredUserView from scratch

### Replay Proof Flow

1. Register user → emits UserRegistered → stored in history + projection built
2. Clear RegisteredUserView (simulate projection loss)
3. Replay from ReferenceEventHistoryStore
4. RegisteredUserView rebuilt with same data

### Test

- `event_history_stores_user_registered_event` — GREEN
- `event_history_replay_rebuilds_registered_user_view` — GREEN
- `event_history_is_reference_proof_only` — confirms no streamId/version/streamVersion

## Validation

| Command                                                   | Result |
|-----------------------------------------------------------|--------|
| `php tooling/events/check-real-dogfooding.php`            | PASS   |
| `php tooling/events/check-event-sourcing-not-default.php` | PASS   |
| PHPUnit replay test                                       | GREEN  |

## Next Allowed Action

V5.7-12: Tooling Gates
