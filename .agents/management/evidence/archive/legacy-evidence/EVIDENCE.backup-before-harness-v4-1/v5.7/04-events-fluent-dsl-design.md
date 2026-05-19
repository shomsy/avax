# V5.7 — 04 Events Fluent DSL Design

**Date:** 2026-05-12

## Public API

### Global Helper Functions

```php
// Register listeners for an event
onEvent(UserRegistered::class)
    ->do(SendWelcomeEmail::class)
    ->do(CreateUserProjection::class);

// With priority
onEvent(UserRegistered::class)
    ->do(ValidateUserRegistration::class, priority: 100)
    ->do(SendWelcomeEmail::class, priority: 50);

// Emit an event
emit(new UserRegistered(
    userId: $userId,
    email: $email,
    registeredAt: $clock->now(),
));
```

### Alternative: events() Helper

```php
// For contexts where global functions are not desired
events()->onEvent(UserRegistered::class)
    ->do(SendWelcomeEmail::class);

events()->emit(new UserRegistered($userId));
```

## DSL Classes

### onEvent() → EventListenerDsl

```php
namespace Avax\Components\Operations\Events\System\Flows\RegisterEventListeners;

final class EventListenerDsl
{
    public function __construct(
        private readonly string $eventClass,
        private readonly ListenerRegistry $registry,
    ) {}

    /**
     * Register a listener for this event.
     *
     * @param  class-string|callable  $listener
     * @param  int  $priority  Higher = earlier execution. Default 0.
     */
    public function do(string|callable $listener, int $priority = 0): self
    {
        $this->registry->subscribe($this->eventClass, $listener, $priority);
        return $this;
    }
}
```

### Global Functions (PublicSurface)

```php
namespace Avax\Components\Operations\Events\System\PublicSurface;

use Avax\Components\Operations\Events\System\Foundation\GlobalEventRegistry;

if (! function_exists('onEvent')) {
    function onEvent(string $eventClass): EventListenerDsl
    {
        return new EventListenerDsl($eventClass, GlobalEventRegistry::instance());
    }
}

if (! function_exists('emit')) {
    function emit(object $event): object
    {
        return GlobalEventRegistry::emitter()->dispatch($event);
    }
}
```

### GlobalEventRegistry (Singleton Boot-Time Only)

```php
namespace Avax\Components\Operations\Events\System\Foundation;

/**
 * Boot-time singleton that holds the canonical listener registry and emitter.
 * Not a service locator — a boot-coordination mechanism.
 * In production, this is resolved through DI during boot and frozen at runtime.
 */
final class GlobalEventRegistry
{
    private static ?self $instance = null;
    private ListenerRegistry $registry;
    private EventEmitter $emitter;

    private function __construct()
    {
        $this->registry = new ListenerRegistry();
        $this->emitter = new EventEmitter($this->registry);
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public static function setInstance(self $instance): void
    {
        self::$instance = $instance;
    }

    public function registry(): ListenerRegistry { return $this->registry; }
    public function emitter(): EventEmitter { return $this->emitter; }
}
```

## DSL Rules

1. **`onEvent()` registers — `emit()` dispatches.** They are separate concepts.
2. `onEvent()` must NOT dispatch.
3. `emit()` must NOT register.
4. Priority: higher number = earlier execution. Default 0.
5. `emit()` returns the dispatched event (PSR-14 compatibility).
6. Global functions are thin PublicSurface wrappers.
7. No PSR types in basic user examples.
8. No container/reflection plumbing in user call sites.

## User API Examples

```php
// Simple registration
onEvent(UserRegistered::class)
    ->do(SendWelcomeEmail::class);

// Multiple listeners with priority
onEvent(UserRegistered::class)
    ->do(AuditUserRegistration::class, priority: 100)
    ->do(SendWelcomeEmail::class, priority: 50)
    ->do(UpdateDashboard::class, priority: 0);

// Closure listeners (for simple cases)
onEvent(OrderPaid::class)
    ->do(fn (OrderPaid $e) => error_log("Order {$e->orderId} paid"));

// Emit events
emit(new UserRegistered($userId, $email, $clock->now()));

// Stoppable event
$result = emit(new OrderValidationFailed($orderId, $reason));
// $result is the same event object, potentially modified by listeners
```

## What is NOT in V5.7 DSL

- Queued/async listeners — ROADMAP
- Listener groups/channels — ROADMAP
- Conditional listeners — ROADMAP
- Event wildcards (`onEvent('user.*')`) — ROADMAP
- Event middleware — ROADMAP
