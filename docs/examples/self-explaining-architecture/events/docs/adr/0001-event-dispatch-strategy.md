# ADR-0001: Event Dispatch Strategy

## Status

**ACCEPTED**

## Context

AvaX needs an event dispatch strategy that allows components to communicate without tight coupling. The decision affects:
- Component boundaries (how do components talk to each other?)
- Performance (event dispatch is on the hot path for many operations)
- Reliability (what happens when a listener fails?)
- Developer experience (how easy is it to add a new listener?)

Options considered:
1. **Direct listener registration**: Flow calls `Dispatcher::listen(Event::class, Listener::class)` at boot time
2. **Attribute-based listener registration**: `#[Listen(UserRegistered::class)]` on listener classes
3. **Convention-based discovery**: Listeners in a `Listeners/` folder are auto-discovered

## Decision

We use **attribute-based listener registration** with compiled dispatch tables.

Listeners are registered as PHP attributes on listener classes. At boot time, all listeners are scanned and compiled into a dispatch table. At runtime, events are dispatched against the compiled table.

```php
#[Listen(UserRegistered::class)]
class SendWelcomeEmail implements Listener
{
    public function handle(UserRegistered $event): void
    {
        // Send welcome email to $event->userEmail
    }
}
```

### Why This Decision

- Attributes keep the listener NEXT TO the event it handles (self-documenting)
- Compiled dispatch tables are fast at runtime (no reflection during hot path)
- PHP attributes are type-safe and IDE-friendly
- The dispatch table can be validated at boot time (detect missing listeners, circular dependencies)

### Trade-offs

- Listener scanning happens at boot time (acceptable: compile-time cost, not runtime)
- Developers must understand PHP attributes (acceptable: PHP 8.x standard)
- Listener conflicts must be detected at boot time (handled by dispatch validation)

## Consequences

- Boot-time listener compilation is mandatory
- Listener failures must be caught and logged (must not break the dispatching Flow)
- Dispatch tables must be cacheable for production
- Listener attributes must be documented in component READMEs
