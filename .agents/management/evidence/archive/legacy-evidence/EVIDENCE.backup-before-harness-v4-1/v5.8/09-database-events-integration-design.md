# V5.8-09: Database Events Integration Design

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Event Integration Design

## Integration Model

### Separation of Concerns

```
Database Lifecycle Registry  ← owns DB-specific lifecycle phases
         ↓
Database Runtime             ← invokes DB lifecycle listeners
         ↓
Listener invokes emit()      ← listener MAY emit canonical AvaX events
         ↓
AvaX Events (V5.7)           ← dispatches to registered event listeners
```

### Required Model

1. **DB lifecycle declarations compile into DB lifecycle registry** — separate from V5.7 Events.
2. **DB lifecycle runtime emits canonical AvaX events internally** — when listeners call `emit()`.
3. **Generic Events component remains canonical event dispatcher** — one event system.
4. **DB lifecycle component owns DB-specific lifecycle phases** — entity, query, transaction.
5. **No second event system** — DB telemetry EventBus must be integrated or deprecated.
6. **No duplicate listener registry for generic events** — DB lifecycle has its own registry.
7. **DB lifecycle registry may adapt into Events registry where appropriate** — future integration.

## Integration Example

```php
// DSL registration
onEntity(User::class)
    ->created(EmitUserRegistered::class);

// Listener bridges DB lifecycle → canonical Events
final readonly class EmitUserRegistered
{
    public function __invoke(EntityCreated $event): void
    {
        emit(new UserRegistered(
            userId: $event->entity->id,
            email: $event->entity->email,
        ));
    }
}

// V5.7 Event listener
onEvent(UserRegistered::class)
    ->do(RecordRegistrationAudit::class)
    ->do(ProjectRegisteredUser::class);
```

### Flow

1. ORM persists new User → fires `EntityCreated` lifecycle event
2. DB lifecycle registry resolves listeners for `EntityCreated(User::class)`
3. `EmitUserRegistered` is invoked with `EntityCreated` event
4. `EmitUserRegistered` calls `emit(new UserRegistered(...))`
5. V5.7 Events dispatches `UserRegistered` to `RecordRegistrationAudit` and `ProjectRegisteredUser`

## Existing Telemetry EventBus

Current state: `components/DataStack/Database/System/Telemetry/Events/EventBus.php` is an isolated event dispatcher for
DB telemetry (ConnectionOpened, QueryExecuted, etc.).

**Decision:** The existing Telemetry EventBus should be evaluated for integration with the canonical Events system.
Options:

- **Option A:** Replace Telemetry EventBus with canonical Events — DB telemetry uses `emit()` for QueryExecuted,
  ConnectionOpened, etc.
- **Option B:** Keep Telemetry EventBus for internal DB telemetry, but add a bridge that can forward to canonical
  Events.
- **Option C:** Deprecate Telemetry EventBus, replace with query lifecycle DSL listeners that emit canonical events.

**Recommended for V5.8:** Option C. The query lifecycle DSL (`onQuery()->executed()`) provides a cleaner path. Existing
Telemetry EventBus listeners can be rewritten as lifecycle DSL listeners that emit canonical events.

## Key Rules

1. **Domain event emission must be explicit** — DB lifecycle events do NOT automatically become domain events.
2. **Database lifecycle event must not automatically become domain event** — listener decides.
3. **afterCommit is preferred for external side effects** — publish, email, HTTP, queue.
4. **Outbox should be used for durable external publication** — not direct publish in afterCommit.
5. **One canonical event dispatcher** — V5.7 Events is the only generic event dispatcher.
6. **DB lifecycle registry is DB-specific** — it handles entity, query, transaction phases.

## Boundary

```
┌─────────────────────────────────────────┐
│  Database Lifecycle                     │
│  ┌───────────────────────────────┐      │
│  │ onEntity(), onQuery(),        │      │
│  │ onTransaction()               │      │
│  │ CompiledDatabaseLifecycleRegistry│   │
│  └───────────────────────────────┘      │
│         ↓                               │
│  ┌───────────────────────────────┐      │
│  │ DB Runtime invokes listeners  │      │
│  │ with EntityCreated, etc.      │      │
│  └───────────────────────────────┘      │
└──────────────┬──────────────────────────┘
               │ listener calls emit()
               ↓
┌─────────────────────────────────────────┐
│  AvaX Events (V5.7)                     │
│  ┌───────────────────────────────┐      │
│  │ onEvent(UserRegistered::class)│      │
│  │   ->do(RecordAudit::class)    │      │
│  └───────────────────────────────┘      │
└─────────────────────────────────────────┘
```

## Next Allowed Action

V5.8-10 Outbox Bridge Design.
