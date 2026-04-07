# Scopes

Scopes are the runtime isolation mechanism of the container.

## Main Units

- `DependencyInjection/Capabilities/Scopes/ScopeManager.php`
- `DependencyInjection/Capabilities/Scopes/ScopeRegistry.php`
- `DependencyInjection/Flows/BeginScope/BeginScope.php`
- `DependencyInjection/Flows/EndScope/EndScope.php`

## Behavior

- singleton instances live across the whole runtime
- scoped instances live only inside the current active scope
- transient instances are not stored

## Public Usage

- `Container::beginScope()`
- `Container::endScope()`
- `Container::scopes()`

## Ownership Rule

Scope mechanics stay in `DependencyInjection/Capabilities/Scopes`.

Scope entry and exit stay in `DependencyInjection/Flows/BeginScope` and `DependencyInjection/Flows/EndScope`
because they are public system actions.
