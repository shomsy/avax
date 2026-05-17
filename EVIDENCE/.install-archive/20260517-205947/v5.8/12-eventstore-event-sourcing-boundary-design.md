# V5.8-12: EventStore and Event Sourcing Boundary Design

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — EventStore / Event Sourcing Boundary

## Clear Definitions

### Database Lifecycle Events

- **Purpose:** Observe DB/entity/transaction operations.
- **Nature:** Optional source of domain events.
- **NOT:** Source of truth.
- **Scope:** V5.8 implementation target.

### Outbox

- **Purpose:** Durable external publication.
- **Nature:** Delivery reliability — ensures events are published exactly once.
- **NOT:** Source of truth.
- **Scope:** V5.8 design, future implementation.

### EventStore

- **Purpose:** Stores domain events as source of truth.
- **Nature:** Append-only log of domain events.
- **Future capability** — NOT V5.8 production scope.

### Event Sourcing Kit

- **Purpose:** Full event sourcing infrastructure.
- **Includes:** Aggregate replay, stream versioning, expected version, optimistic concurrency, snapshots, upcasters,
  projections/replay.
- **Future capability** — NOT V5.8 production scope.

## Decision Matrix

| Need                                 | Use                             | V5.8 Scope           |
|--------------------------------------|---------------------------------|----------------------|
| Observe entity save/delete           | DB lifecycle event              | DESIGN + IMPLEMENT   |
| React after transaction commit       | Transaction afterCommit         | DESIGN + IMPLEMENT   |
| Publish externally reliably          | Outbox                          | DESIGN only          |
| Build read model                     | Projection                      | DESIGN only          |
| Use event history as source of truth | EventStore + Event Sourcing Kit | DESIGN boundary only |
| Simple CRUD                          | Normal DB/ORM/QueryBuilder      | Unchanged            |

## Rules

1. **V5.8 must not claim production Event Sourcing Kit.**
2. **V5.8 may prepare interfaces/roadmap only.**
3. **EventStore should be a separate future capability** — or clear package, not hidden inside QueryBuilder.
4. **DB component should support EventStore storage** — but not become event-sourcing-only DB.
5. **Normal CRUD/QueryBuilder/ORM remain first-class** — event sourcing is an option, not a requirement.

## EventStore Interface Sketch (Future)

```php
interface EventStore
{
    public function append(string $streamName, array $events, ?int $expectedVersion = null): void;
    public function load(string $streamName, int $fromVersion = 0): array;
    public function loadFrom(string $streamName, int $fromVersion): array;
    public function streamNames(): iterable;
}

interface EventSourcingKit
{
    public function aggregate(string $streamName): AggregateRoot;
    public function snapshot(string $streamName, Snapshot $snapshot): void;
    public function loadSnapshot(string $streamName): ?Snapshot;
}
```

## Future EventStore Owner

- **Path:** `components/DataStack/EventStore/` or `components/Operations/EventSourcing/`
- **Not V5.8** — explicitly marked as ROADMAP.
- **Depends on:** DB lifecycle events (for observing entity changes), Outbox (for durable publication), Projections (for
  read models).

## Relationship Diagram

```
┌───────────────────────┐
│  DB Lifecycle Events  │  ← observes save/update/delete
└──────────┬────────────┘
           │ fires EntityCreated, etc.
           ↓
┌───────────────────────┐
│  Listener calls       │  ← explicit decision to emit domain event
│  emit(DomainEvent)    │
└──────────┬────────────┘
           │
     ┌─────┴─────┐
     ↓           ↓
┌─────────┐ ┌──────────┐
│ Outbox  │ │ EventStore│  ← future, NOT V5.8
│ (deliver│ │ (source   │
│  reliably) │  of truth)│
└─────────┘ └──────────┘
```

## V5.8 Explicit Non-Claims

V5.8 must NOT claim:

- [ ] Production Event Sourcing Kit
- [ ] EventStore implementation
- [ ] Aggregate replay
- [ ] Stream versioning
- [ ] Expected version checking
- [ ] Optimistic concurrency via event sourcing
- [ ] Snapshots
- [ ] Upcasters
- [ ] Projection rebuild from event stream

V5.8 MAY claim:

- [x] Database lifecycle event observation
- [x] Entity lifecycle hooks (creating, created, updating, updated, deleting, deleted)
- [x] Transaction lifecycle hooks (afterCommit, afterRollback)
- [x] Query lifecycle hooks (executed, slow)
- [x] Outbox bridge design (interfaces, integration path)
- [x] Projection bridge design (existing CQRS proof, future DSL)
- [x] EventStore/EventSourcing boundary definition

## Next Allowed Action

V5.8-13 Transaction Model Audit.
