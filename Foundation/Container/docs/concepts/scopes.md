# Scopes

Scopes are the runtime isolation mechanism of the container.

## Main Units

- `Capabilities/Scopes/ScopeManager.php`
- `Capabilities/Scopes/ScopeRegistry.php`
- `Flows/BeginScope/BeginScope.php`
- `Flows/EndScope/EndScope.php`

## Behavior

- singleton instances live across the whole runtime
- scoped instances live only inside the current active scope
- transient instances are not stored

## Public Usage

- `Container::beginScope()`
- `Container::endScope()`
- `Container::scopes()`

## Ownership Rule

Scope mechanics stay in `Capabilities/Scopes`.

Scope entry and exit stay in `Flows/BeginScope` and `Flows/EndScope` because they are public system actions.
