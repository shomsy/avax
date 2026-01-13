# ServiceProvider (Base)

## Quick Summary

- Abstract base implementing `ServiceProviderInterface` with empty `register()`/`boot()` defaults.
- Provides a consistent constructor signature with the Container injected.

### For Humans: What This Means

Extend this class to create providers; override `register()` (and `boot()` if needed).

## Terminology

- **Provider**: Class that declares bindings and optional boot logic.
- **Deterministic order**: Providers run in the order you list them in `AppFactory::http()`.

### For Humans: What This Means

Providers are just classes you list; they don’t auto-discover.

## Think of It

Like a plug-in slot: you extend it, fill in bindings, and the factory executes in order.

### For Humans: What This Means

It standardizes how you add modules to the app.

## Story Example

```php
final class RouterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Router::class, Router::class);
    }
}
```

### For Humans: What This Means

Your providers can be minimal—just declare what to bind.

## For Dummies

1) Extend `Providers\ServiceProvider`.
2) Override `register()` to bind services.
3) Optionally override `boot()` for post-bind init.

### For Humans: What This Means

Subclass, bind, optionally init. Done.

## How It Works (Technical)

- Implements `ServiceProviderInterface`.
- Stores the Container (`$app`) for use in child classes.
- `register()` and `boot()` are no-ops by default.

### For Humans: What This Means

You inherit the contract and only write what you need.

## Architecture Role

- Lives in `Providers/` as the shared base for all providers.
- Used by all framework/app providers to standardize boot flow.

### For Humans: What This Means

This is the common foundation for every provider in the system.

## Methods

- `__construct(Container $app)`: stores container.
- `register(): void`: override for bindings.
- `boot(): void`: override for init.

### For Humans: What This Means

You get the container, then you declare bindings and optional startup work.

## Risks & Trade-offs

- If you forget to override `register()`, nothing is bound.
- Deterministic order means missing providers break dependencies.

### For Humans: What This Means

Be explicit; keep an ordered provider list.

## Related Files & Folders

- `Features/Core/Contracts/ServiceProviderInterface.php`: Contract implemented here.
- `Core/AppFactory.php`: Runs these providers.

### For Humans: What This Means

Use the base to comply with the contract; factory handles execution order.
