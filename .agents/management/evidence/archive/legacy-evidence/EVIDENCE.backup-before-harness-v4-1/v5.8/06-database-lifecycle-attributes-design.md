# V5.8-06: Database Lifecycle Attributes Design

**Date:** 2026-05-13
**Stage:** V5.8 Design Lock — Attributes Design

## Decision: Design Now, Implement in V5.8.x

Attributes for DB lifecycle hooks are designed now but implementation is deferred to V5.8.x (after fluent DSL is
stable).

V5.8 GREEN must NOT claim attribute lifecycle support.

## Candidate Attributes

```php
#[EntityCreating(User::class)]
#[EntityCreated(User::class)]
#[EntityUpdating(User::class)]
#[EntityUpdated(User::class)]
#[EntitySaving(User::class)]
#[EntitySaved(User::class)]
#[EntityDeleting(User::class)]
#[EntityDeleted(User::class)]

#[QueryExecuted]
#[SlowQuery(thresholdMs: 100)]

#[TransactionAfterCommit]
#[TransactionAfterRollback]
```

## Attribute Rules

1. Attributes are **declaration only**.
2. Runtime MUST NOT scan attributes.
3. Compile/build path may scan attributes.
4. DB lifecycle attributes must compile into the same lifecycle registry as fluent DSL.
5. Do NOT implement decorative attributes.
6. Do NOT expose unsupported queue/async/afterCommit modes as production-ready unless real.
7. Attribute names describe lifecycle fact, not generic handler.
8. Attributes target classes (listener classes), not methods.

## Attribute Sketch

```php
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class EntityCreating
{
    public function __construct(
        public string $entityClass,
        public int $priority = 0,
    ) {}
}

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class EntityCreated
{
    public function __construct(
        public string $entityClass,
        public int $priority = 0,
    ) {}
}

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class TransactionAfterCommit
{
    public function __construct(
        public int $priority = 0,
    ) {}
}

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class SlowQuery
{
    public function __construct(
        public int $thresholdMs = 100,
    ) {}
}
```

## Example Usage (Deferred)

```php
#[EntityCreated(User::class, priority: 10)]
final readonly class EmitUserRegistered
{
    public function __invoke(EntityCreated $event): void
    {
        emit(new UserRegistered(userId: $event->entity->id));
    }
}

#[TransactionAfterCommit]
final readonly class PublishOutboxMessages
{
    public function __invoke(AfterCommit $event): void
    {
        // publish outbox messages
    }
}
```

## Compilation Path (Deferred)

The compile/build path will:

1. Scan registered listener classes for DB lifecycle attributes.
2. Merge attribute declarations with fluent DSL declarations.
3. Compile into `CompiledDatabaseLifecycleRegistry`.
4. No runtime attribute reflection.

## V5.8 Scope

- **Design: DONE** — attribute names, targets, compilation strategy defined.
- **Implementation: DEFERRED** to V5.8.x.
- V5.8 GREEN status must NOT claim attribute support.

## Next Allowed Action

V5.8-07 Compiled Lifecycle Registry Design.
