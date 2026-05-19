# V5.8-11: Projection Bridge Design

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Projection Bridge Design

## V5.8 Scope Decision

**Decision: Design bridge and stage plan. Do not implement full projection engine.**

V5.8 designs how DB lifecycle and events support projections. The existing MessageBus Projection capability provides a starting point. Full projection engine is future work.

## Rules

1. **Projection listener builds read model from event** — event in, read model out.
2. **Projection must be idempotent if replayable** — replaying same event produces same state.
3. **Projection failure must be observable** — not silently swallowed.
4. **Projection rebuild belongs to future projection capability** — unless minimal reference proof exists.
5. **CQRS read model must not create generic CQRS folder theater** — ownership inside specific flows.

## Existing Infrastructure

### MessageBus Projection

`components/Operations/MessageBus/System/Capabilities/Projection/Projection.php`

Already exists: Event projection that transforms envelopes into read model state.

### Database Query Projections

`components/DataStack/Database/System/Capabilities/Query/Projections/Projection.php`

Already exists: Maps query rows to typed objects (different concept — DTO projection, not event projection).

### V5.7 CQRS Projection Proof

`ProjectRegisteredUser` listener in SecureRegistrationApi already builds `RegisteredUserView` from `UserRegistered` events.

## Bridge Design

### Event-Driven Projection

```php
// V5.7 Event listener that builds read model
onEvent(UserRegistered::class)
    ->do(ProjectRegisteredUser::class);

final readonly class ProjectRegisteredUser
{
    public function __invoke(UserRegistered $event): void
    {
        // Build RegisteredUserView from event
        // This is already implemented in V5.7
    }
}
```

### Query for Read Model

```php
// Read from the projection
final readonly class ReadRegisteredUser
{
    public function __invoke(ReadUserQuery $query): RegisteredUserView
    {
        // Query the read model
        // This is already implemented in V5.7
    }
}
```

### Future Projection Bridge

```php
// Future: dedicated projection registration
onProjection()
    ->from(UserRegistered::class)
    ->build(RegisteredUserView::class)
    ->using(BuildRegisteredUserView::class);

// Future: projection rebuild
onProjection()
    ->rebuild(RevenueProjection::class)
    ->fromStream(OrderPaid::class)
    ->using(RebuildRevenueProjection::class);
```

## Projection Examples

| Event | Projection | Type |
|-------|-----------|------|
| `UserRegistered` | `RegisteredUserView` | User read model |
| `OrderPaid` | `RevenueProjection` | Aggregated metric |
| `QueryExecuted` | `SlowQueryReport` | Telemetry projection |
| `TransactionCommitted` | `TransactionAudit` | Audit projection |

## V5.8 Scope Summary

| Aspect | V5.8 Scope | Future Scope |
|--------|-----------|-------------|
| Projection bridge design | DONE | — |
| Existing CQRS projection proof | Already in V5.7 | — |
| onProjection() DSL | Design | Implement V5.9+ |
| Projection rebuild | Design | V5.9+ |
| Projection failure handling | Design | V5.9+ |
| Full projection engine | Design | V5.9+ |

## Next Allowed Action

V5.8-12 EventStore and Event Sourcing Boundary Design.
