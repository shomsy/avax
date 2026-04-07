# Scopes

Scopes are the runtime isolation mechanism of the container.

## Main Units

- `DependencyInjection/Capability/Scopes/ScopeManager.php`
- `DependencyInjection/Capability/Scopes/ScopeRegistry.php`
- `DependencyInjection/Flow/BeginScope/BeginScope.php`
- `DependencyInjection/Flow/EndScope/EndScope.php`

## Behavior

- singleton instances live across the whole runtime
- scoped instances live only inside the current active scope
- transient instances are not stored

## Public Usage

- `Container::beginScope()`
- `Container::endScope()`
- `Container::scopes()`

## Ownership Rule

Scope mechanics stay in `DependencyInjection/Capability/Scopes`.

Scope entry and exit stay in `DependencyInjection/Flow/BeginScope` and `DependencyInjection/Flow/EndScope`
because they are public system actions.
