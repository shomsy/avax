# V5.8-07: Compiled Database Lifecycle Registry Design

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Compiled Registry Design

## Concepts

### Foundation Types

```php
enum EntityLifecyclePhase: string
{
    case Creating = 'creating';
    case Created = 'created';
    case Updating = 'updating';
    case Updated = 'updated';
    case Saving = 'saving';
    case Saved = 'saved';
    case Deleting = 'deleting';
    case Deleted = 'deleted';
    case Restored = 'restored';
    case FailedToSave = 'failedToSave';
    case FailedToDelete = 'failedToDelete';
}

enum QueryLifecyclePhase: string
{
    case Executing = 'executing';
    case Executed = 'executed';
    case Slow = 'slow';
    case Failed = 'failed';
}

enum TransactionLifecyclePhase: string
{
    case Beginning = 'beginning';
    case Committed = 'committed';
    case AfterCommit = 'afterCommit';
    case RolledBack = 'rolledBack';
    case AfterRollback = 'afterRollback';
    case Failed = 'failed';
}

enum LifecycleSource: string
{
    case Dsl = 'dsl';
    case Attribute = 'attribute';
    case Configuration = 'configuration';
}

enum LifecycleExecutionMode: string
{
    case Sync = 'sync';
    // Async is ROADMAP for V5.8+
}
```

### Registration Types

```php
final readonly class EntityLifecycleRegistration
{
    public function __construct(
        public string $entityClass,
        public EntityLifecyclePhase $phase,
        public string $listener,
        public int $priority = 0,
        public LifecycleSource $source = LifecycleSource::Dsl,
        public LifecycleExecutionMode $mode = LifecycleExecutionMode::Sync,
    ) {}
}

final readonly class QueryLifecycleRegistration
{
    public function __construct(
        public QueryLifecyclePhase $phase,
        public string $listener,
        public int $priority = 0,
        public LifecycleSource $source = LifecycleSource::Dsl,
        public LifecycleExecutionMode $mode = LifecycleExecutionMode::Sync,
        public ?int $thresholdMs = null,  // for Slow queries
    ) {}
}

final readonly class TransactionLifecycleRegistration
{
    public function __construct(
        public TransactionLifecyclePhase $phase,
        public string $listener,
        public int $priority = 0,
        public LifecycleSource $source = LifecycleSource::Dsl,
        public LifecycleExecutionMode $mode = LifecycleExecutionMode::Sync,
    ) {}
}
```

### Compiled Registry

```php
final class CompiledDatabaseLifecycleRegistry
{
    // Entity listeners: entityClass -> phase -> sorted list of listeners
    private array $entityListeners = [];

    // Query listeners: phase -> sorted list of listeners
    private array $queryListeners = [];

    // Transaction listeners: phase -> sorted list of listeners
    private array $transactionListeners = [];

    private bool $frozen = false;

    // Registration methods (boot-time only)
    public function registerEntity(EntityLifecycleRegistration $registration): void
    public function registerQuery(QueryLifecycleRegistration $registration): void
    public function registerTransaction(TransactionLifecycleRegistration $registration): void

    // Resolution methods (runtime)
    public function entityListenersFor(string $entityClass, EntityLifecyclePhase $phase): array
    public function queryListenersFor(QueryLifecyclePhase $phase): array
    public function transactionListenersFor(TransactionLifecyclePhase $phase): array

    // Freeze
    public function freeze(): void
    public function isFrozen(): bool
}
```

## Rules

1. **Fluent DSL + attributes + config compile into one registry.**
2. **No runtime attribute reflection** — attributes scanned at compile-time only.
3. **Higher priority runs first** — priority descending sort.
4. **Same priority preserves registration order** — deterministic tie-breaking.
5. **Registry must be boot-time frozen** — after compilation, no new registrations.
6. **Long-lived runtimes must not leak request state** — transaction-scoped buffers reset.
7. **Disk compiled cache is optional** — not required for V5.8.
8. **Registry is immutable after freeze** — any registration after freeze throws.

## Compilation Flow

```
onEntity(User::class)->created(ListenerA::class)   ← DSL registration
#[EntityCreated(User::class)] ListenerB::class     ← attribute (compile-time scan)
                                                       ↓
                                        CompileDatabaseLifecycleRegistry
                                                       ↓
                                    CompiledDatabaseLifecycleRegistry (frozen)
                                                       ↓
                                         Runtime DB execution reads registry
```

## Ordering Example

```
Registrations:
1. onEntity(User::class)->created(A::class, priority: 10)
2. onEntity(User::class)->created(B::class, priority: 5)
3. onEntity(User::class)->created(C::class, priority: 10)  // registered after A

Result for User::class → Created:
1. A (priority 10, registered first)
2. C (priority 10, registered second)
3. B (priority 5)
```

## Next Allowed Action

V5.8-08 Database Lifecycle Execution Semantics.
