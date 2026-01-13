# ServiceProviderInterface

## Quick Summary

- Contract defining deterministic service providers: constructor receives Container, with `register()` and `boot()`
  hooks.
- Replaces the old auto-scan/provider abstractions for explicit provider lists.

### For Humans: What This Means

Every provider now follows the same simple shape: construct with container, register bindings, then boot if needed.

## Terminology

- **register()**: Bind services/definitions into the container.
- **boot()**: Optional post-registration initialization/resolution.
- **Deterministic providers**: Explicitly listed providers in boot order.

### For Humans: What This Means

First phase: declare bindings. Second phase: run init logic. Order is controlled by your list.

## Think of It

Like a two-step appliance setup: plug it in (register), then turn it on (boot).

### For Humans: What This Means

No magic scans—just call register then boot in the order you choose.

## Story Example

```php
final class RouterServiceProvider implements ServiceProviderInterface
{
    public function __construct(private Container $app) {}
    public function register(): void { /* bindings */ }
    public function boot(): void { /* optional */ }
}
```

### For Humans: What This Means

All providers look and behave the same, making boot predictable.

## For Dummies

1) Constructor gets Container.
2) `register()` adds bindings.
3) `boot()` runs after all providers register.

### For Humans: What This Means

Setup then turn on.

## How It Works (Technical)

- Interface enforces constructor signature `__construct(Container $app)`.
- AppFactory instantiates providers in order, calls `register()`, then `boot()`.

### For Humans: What This Means

AppFactory controls order; providers implement the hooks.

## Architecture Role

- Lives in `Features/Core/Contracts` as the provider contract.
- Consumed by `AppFactory::http()` and implemented by concrete providers.

### For Humans: What This Means

This is the standard every provider must follow in the new boot flow.

## Methods

- `__construct(Container $app)`: receive container.
- `register(): void`: add bindings.
- `boot(): void`: run initialization.

### For Humans: What This Means

Provide a container, declare bindings, optionally initialize.

## Risks & Trade-offs

- Requires manual provider lists; missing providers cause runtime failures.
- Enforces constructor signature; legacy providers may need edits.

### For Humans: What This Means

Update old providers to match this interface; keep a canonical provider list.

## Related Files & Folders

- `Providers/ServiceProvider.php`: Base class implementing this interface.
- `Core/AppFactory.php`: Instantiates and executes providers.

### For Humans: What This Means

Use the base class for convenience; factory runs providers in your specified order.
