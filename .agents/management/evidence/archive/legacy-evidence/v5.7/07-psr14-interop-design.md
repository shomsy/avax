# V5.7 — 07 PSR-14 Interop Design

**Date:** 2026-05-12

## Dependency Audit

### Current composer.json

`psr/event-dispatcher` is **NOT** currently installed.

**Decision:** Make `psr/event-dispatcher` an optional `suggest` dependency, not a hard `require`.

**Why:**

- AvaX should work without PSR-14 for simple use cases
- PSR-14 compatibility is valuable for ecosystem interop but not required for core functionality
- Adapter pattern allows PSR-14 when the package is present
- Follows the same pattern as `ext-redis` and `ext-memcached` — available when installed, graceful when not

### Composer Constraint

```json
"suggest": {
    "psr/event-dispatcher": "^1.0 — PSR-14 event dispatcher interop"
}
```

When `psr/event-dispatcher` is installed, AvaX automatically provides PSR-14 adapters.

## PSR-14 Concepts

### Interfaces to Implement/Adapt

| PSR-14 Interface                                | AvaX Adaptation                  | Notes                                            |
|-------------------------------------------------|----------------------------------|--------------------------------------------------|
| `Psr\EventDispatcher\EventDispatcherInterface`  | `Psr14EventDispatcherAdapter`    | Wraps AvaX EventEmitter                          |
| `Psr\EventDispatcher\ListenerProviderInterface` | `Psr14ListenerProviderAdapter`   | Wraps CompiledListenerRegistry                   |
| `Psr\EventDispatcher\StoppableEventInterface`   | User events optionally implement | AvaX checks via `instanceof` only if PSR present |

### AvaX EventDispatcher → PSR-14 Adapter

```php
namespace Avax\Components\Operations\Events\System\Capabilities\Psr14;

use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * PSR-14 adapter — wraps AvaX EventEmitter as PSR-14 dispatcher.
 * Only available when psr/event-dispatcher is installed.
 */
final readonly class Psr14EventDispatcherAdapter implements EventDispatcherInterface
{
    public function __construct(
        private EventEmitter $avaxEmitter,
    ) {}

    /**
     * @param  object  $event
     * @return object The event, possibly modified by listeners
     */
    public function dispatch(object $event): object
    {
        return $this->avaxEmitter->dispatch($event);
    }
}
```

### AvaX ListenerProvider → PSR-14 Adapter

```php
namespace Avax\Components\Operations\Events\System\Capabilities\Psr14;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * PSR-14 listener provider adapter.
 */
final readonly class Psr14ListenerProviderAdapter implements ListenerProviderInterface
{
    public function __construct(
        private CompiledListenerRegistry $registry,
    ) {}

    public function getListenersFor(object $event): iterable
    {
        $compiled = $this->registry->getListenersFor($event::class);
        foreach ($compiled as $listener) {
            yield fn (object $e) => $this->invokeListener($listener, $e);
        }
    }
}
```

### Stoppable Events

```php
// User event optionally implementing PSR-14
final class OrderValidation implements \Psr\EventDispatcher\StoppableEventInterface
{
    public bool $valid = true;

    public function isPropagationStopped(): bool
    {
        return ! $this->valid; // Stop if a listener marked it invalid
    }
}
```

**AvaX dispatch behavior:**

```php
// In EventDispatcher::dispatch()
if ($event instanceof \Psr\EventDispatcher\StoppableEventInterface
    && $event->isPropagationStopped()) {
    break;
}
```

## Design Decisions

### 1. Direct Implementation vs Adapter Wrapper

**Decision:** Adapter wrapper.

AvaX implements its own `EventEmitter` and `EventDispatcher`. When PSR-14 is present, adapter classes wrap the AvaX
implementations to expose PSR-14 interfaces.

**Why:**

- AvaX API stays independent of PSR
- Users use AvaX DSL (`onEvent()->do()`, `emit()`) without seeing PSR types
- PSR-14 interop is for ecosystem compatibility, not user-facing API
- Adapter can be swapped if PSR-14 spec changes

### 2. Dependency Policy

**Decision:** Optional / suggested.

```
psr/event-dispatcher: suggested (not required)
```

**Why:**

- Core AvaX functionality works without PSR-14
- PSR-14 adapters activate when the package is available
- `class_exists(\Psr\EventDispatcher\EventDispatcherInterface::class)` guard in adapter registration
- No duplicate event system — PSR adapter wraps AvaX, does not replace it

### 3. User DSL Does Not Expose PSR Plumbing

**Decision:** Strict.

User code uses:

```php
onEvent(UserRegistered::class)->do(SendWelcomeEmail::class);
emit(new UserRegistered($userId));
```

NOT:

```php
$provider = new Psr14ListenerProviderAdapter(...);
$dispatcher = new PsrEventDispatcher($provider);
$dispatcher->dispatch($event);
```

PSR-14 is for:

- Framework consumers that expect PSR-14 dispatchers
- Testing with PSR-14 test doubles
- Integration with third-party PSR-14 libraries

## Acceptance Criteria

- [ ] PSR dispatch($event) returns event
- [ ] Listener provider returns iterable listeners
- [ ] Stopped propagation halts later listeners
- [ ] No duplicate event system — PSR adapter wraps AvaX
- [ ] AvaX works without PSR-14 installed
- [ ] AvaX adapters activate when PSR-14 is installed
- [ ] User DSL does not require PSR types
