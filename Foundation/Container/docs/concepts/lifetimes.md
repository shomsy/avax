# Lifetimes

Lifetime decides whether a resolved object is reused.

## Supported Lifetimes

- `shared`
- `scoped`
- `transient`

## Main Files

- `Scopes/Lifetimes/SharedLifetime.php`
- `Scopes/Lifetimes/ScopedLifetime.php`
- `Scopes/Lifetimes/TransientLifetime.php`
- `Scopes/Lifetimes/Attributes/Singleton.php`

## Practical Meaning

- `shared`: store once and reuse for the whole runtime
- `scoped`: store once per active scope
- `transient`: do not store; rebuild every time

`#[Singleton]` marks an autowired class as shared by default.
