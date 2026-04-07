# Lifetimes

Lifetime decides where a resolved instance is reused.

## Supported Lifetimes

- `singleton`
- `scoped`
- `transient`

## Main Units

- `DependencyInjection/Capabilities/Scopes/Lifetimes/ServiceLifetime.php`
- `DependencyInjection/Capabilities/Scopes/Lifetimes/LifecycleResolver.php`
- `DependencyInjection/Capabilities/Scopes/Lifetimes/LifecycleStrategyRegistry.php`
- `DependencyInjection/Capabilities/Scopes/Lifetimes/Strategies/*`

## Practical Meaning

- `singleton`: store globally
- `scoped`: store inside the current active scope
- `transient`: never store, always rebuild

## Ownership Rule

Lifetime is a scope capability, not a public flow and not generic kernel glue.
