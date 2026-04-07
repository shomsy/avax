# Lifetimes

Lifetime decides whether a resolved object is reused.

## Supported Lifetimes

- `shared`
- `scoped`
- `transient`

## Main Files

- `DependencyInjection/Scopes/Lifetimes/SharedLifetime.php`
- `DependencyInjection/Scopes/Lifetimes/ScopedLifetime.php`
- `DependencyInjection/Scopes/Lifetimes/TransientLifetime.php`
- `DependencyInjection/Scopes/Lifetimes/Attributes/Singleton.php`

## Practical Meaning

- `shared`: store once and reuse for the whole runtime
- `scoped`: store once per active scope
- `transient`: do not store; rebuild every time

`#[Singleton]` marks an autowired class as shared by default.
