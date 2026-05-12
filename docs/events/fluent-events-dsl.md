# AvaX Events — Fluent DSL & PSR-14 Interop

**Status:** V5.7 Design Lock — NOT YET IMPLEMENTED
**Date:** 2026-05-12

## What Are Events?

Events are **facts** that something already happened. They are plain readonly objects:

```php
final readonly class UserRegistered
{
    public function __construct(
        public string $userId,
        public string $email,
        public DateTimeImmutable $registeredAt,
    ) {}
}
```

## Command vs Event vs Listener

| Concept      | Tense      | Purpose                                | Example            |
|--------------|------------|----------------------------------------|--------------------|
| **Command**  | Imperative | Ask the system to DO something         | `RegisterUser`     |
| **Event**    | Past       | Record that something ALREADY HAPPENED | `UserRegistered`   |
| **Listener** | Reactive   | React to an event                      | `SendWelcomeEmail` |

Flow: **Command** → action happens → **Event** → **Listener** reacts.

## Quick Start

### 1. Register Listeners

```php
use function Avax\Components\Operations\Events\System\PublicSurface\onEvent;

onEvent(UserRegistered::class)
    ->do(SendWelcomeEmail::class)
    ->do(CreateUserProjection::class);
```

### 2. Emit Events

```php
use function Avax\Components\Operations\Events\System\PublicSurface\emit;

emit(new UserRegistered(
    userId: $userId,
    email: $email,
    registeredAt: $clock->now(),
));
```

### 3. Use Attributes

```php
use Avax\Components\Operations\Events\System\Foundation\ListensTo;

#[ListensTo(UserRegistered::class)]
final readonly class SendWelcomeEmail
{
    public function __invoke(UserRegistered $event): void
    {
        // Send the welcome email
    }
}
```

## Fluent DSL

### onEvent()->do()

```php
// Simple registration
onEvent(UserRegistered::class)
    ->do(SendWelcomeEmail::class);

// Multiple listeners with priority
onEvent(UserRegistered::class)
    ->do(AuditUserRegistration::class, priority: 100)
    ->do(SendWelcomeEmail::class, priority: 50)
    ->do(UpdateDashboard::class);

// Closure listeners
onEvent(OrderPaid::class)
    ->do(fn (OrderPaid $e) => error_log("Order {$e->orderId} paid"));
```

### emit()

```php
// Emit an event
$event = emit(new UserRegistered($userId, $email, $clock->now()));

// emit() returns the dispatched event
// If no listeners are registered, emit() returns the event unchanged
```

## Priority

Listeners execute in **priority order** (higher number = earlier execution):

```php
onEvent(UserRegistered::class)
    ->do(AuditListener::class, priority: 100)    // First
    ->do(EmailListener::class, priority: 50)     // Second
    ->do(LogListener::class);                     // Last (default 0)
```

Tie-breaking: registration order (first registered executes first among same priority).

## No Listener Case

```php
$event = emit(new RareEvent());
// No error — returns the event unchanged
// Events with no listeners are valid
```

## Listener Failure

By default, if a listener throws an exception:

- The exception propagates
- Later listeners do NOT execute
- No silent swallowing

```php
onEvent(UserRegistered::class)
    ->do(WorkingListener::class)    // Executes
    ->do(FailingListener::class)   // Throws
    ->do(NeverExecuted::class);     // Never runs
```

## Stoppable Events

Events can optionally implement PSR-14 `StoppableEventInterface`:

```php
final class OrderValidation implements \Psr\EventDispatcher\StoppableEventInterface
{
    public bool $isValid = true;

    public function isPropagationStopped(): bool
    {
        return ! $this->isValid;
    }
}

// A listener can stop further processing:
#[ListensTo(OrderValidation::class)]
final readonly class BlockInvalidOrders
{
    public function __invoke(OrderValidation $event): void
    {
        if ($event->orderId === 'blacklisted') {
            $event->isValid = false; // Later listeners are skipped
        }
    }
}
```

## PSR-14 Interop

When `psr/event-dispatcher` is installed, AvaX provides PSR-14 adapters:

```php
// AvaX DSL works normally
onEvent(UserRegistered::class)->do(SendWelcomeEmail::class);
emit(new UserRegistered($userId));

// PSR-14 adapter wraps AvaX internally
// Users do not need to interact with PSR-14 directly
```

PSR-14 is **optional**. AvaX works without it. The adapter activates automatically when the package is available.

## Compiled Registry

All listener declarations (DSL + attributes) compile into a **single canonical listener registry** at boot time.

**Runtime dispatch uses NO reflection.** The compiled registry provides pre-resolved listener metadata.

This means:

- Fast dispatch (no attribute scanning per event)
- Deterministic listener order
- Testable compilation

## What Is NOT in V5.7

These are ROADMAP items:

- **Async/queued listeners** — future phase
- **Database lifecycle events** (`onEntity()->beforeSave()`) — V5.8
- **Transaction events** (`onTransaction()->afterCommit()`) — V5.8
- **Boot DSL** (`avax()->events()`) — V5.9
- **FailureBoundary integration** with events — future
- **Event wildcards** (`onEvent('user.*')`) — future
- **Listener groups/channels** — future
- **Method-level `#[ListensTo]`** — future

## Design Document

Full design: `EVIDENCE/v5.7/`

| Document                                  | Content                         |
|-------------------------------------------|---------------------------------|
| `01-existing-event-system-audit.md`       | Current event systems inventory |
| `02-events-owner-decision.md`             | Canonical owner decision        |
| `03-event-model-decision.md`              | Event/listener/command model    |
| `04-events-fluent-dsl-design.md`          | DSL API design                  |
| `05-listens-to-attribute-design.md`       | Attribute design                |
| `06-compiled-listener-registry-design.md` | Compiled registry design        |
| `07-psr14-interop-design.md`              | PSR-14 adapter design           |
| `08-event-dispatch-semantics.md`          | Dispatch behavior rules         |
| `09-future-compatibility-design.md`       | Future phase enablement         |
| `10-events-tooling-gates-design.md`       | Governance gate design          |
| `11-proposed-events-architecture-tree.md` | Proposed file tree              |
| `12-v5.7-implementation-stage-plan.md`    | Implementation stages           |
