# AvaX Events — Fluent DSL, Compiled Registry, Dispatch Runtime & PSR-14 Interop

**Status:** V5.7-04 through V5.7-08 IMPLEMENTED / GREEN
**Date:** 2026-05-12

## What Are Events?

Events are **facts** that something already happened. They are plain objects — no `EventInterface` required:

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

Listeners are invokable classes — no `ListenerInterface` required:

```php
final readonly class SendWelcomeEmail
{
    public function __invoke(UserRegistered $event): void
    {
        // Send the welcome email
    }
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

### Boot-Time Compilation

```php
use Avax\Components\Operations\Events\System\Capabilities\Registry\ListenerRegistry;
use Avax\Components\Operations\Events\System\Configuration\RegisterEventDependencies;

$registry = new ListenerRegistry();

// DSL registrations
onEventSetRegistry($registry);
onEvent(UserRegistered::class)->do(SendWelcomeEmail::class);

// Compile DSL + #[ListensTo] attributes into CompiledListenerRegistry
RegisterEventDependencies::compileAndWire(
    $registry,
    listenerClasses: [SendWelcomeEmail::class, AuditListener::class],
);

// Now emit() uses the compiled registry
emit(new UserRegistered($userId, $email, $clock->now()));
```

### Runtime Dispatch Flow

```
emit(new Event())
  → EventEmitter
  → CompiledListenerRegistry (frozen, no reflection)
  → ResolveEventListeners (class-string → callable, once per listener)
  → InvokeEventListener
  → return event
```

### Source Tracking

Each compiled listener tracks its source:

- `ListenerSource::Dsl` — registered via `onEvent()->do()`
- `ListenerSource::Attribute` — declared via `#[ListensTo]`
- `ListenerSource::Configuration` — registered via configuration (future)

Both sources appear in the same registry. Priority ordering works across sources.

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

### PSR-14 Adapters

- `Psr14EventDispatcherAdapter` — implements `Psr\EventDispatcher\EventDispatcherInterface`, delegates to AvaX `EventEmitter`
- `Psr14ListenerProviderAdapter` — implements `Psr\EventDispatcher\ListenerProviderInterface`, delegates to AvaX `CompiledListenerRegistry`
- Stoppable events (`Psr\EventDispatcher\StoppableEventInterface`) are respected via duck-typing

**Important:** AvaX users should use `emit()` and `onEvent()`, not PSR-14 plumbing. PSR-14 adapters exist for ecosystem interop.

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
- **Disk-persisted compiled registry** — ROADMAP (in-memory only for V5.7)
- **Container-based listener resolution** — ROADMAP (simple instantiation for V5.7)
- **CQRS projection dogfooding** — not yet
- **Event-history proof** — not yet
- **SecureRegistrationApi event dogfooding** — not yet

## Implementation Evidence

V5.7-04 through V5.7-08 are implemented and validated.

| Stage | Status | Evidence |
|-------|--------|----------|
| V5.7-04 emit() Surface | GREEN | 49 new tests, emit(object): object |
| V5.7-05 ListensTo Attribute | GREEN | #[ListensTo] attribute + compile |
| V5.7-06 Compiled Listener Registry | GREEN | DSL + attribute convergence |
| V5.7-07 Dispatch Runtime | GREEN | EventEmitter → CompiledListenerRegistry → InvokeEventListener |
| V5.7-08 PSR-14 Adapter | GREEN | psr/event-dispatcher adapters |

Evidence files: `EVIDENCE/v5.7/34-` through `EVIDENCE/v5.7/42-`
Tests: `tests/Unit/Components/Operations/Events/EventsRuntimeClosureTest.php` (49 tests)
