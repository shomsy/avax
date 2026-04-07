# Scopes

Scopes are the isolation boundary for scoped services.

## Main Files

- `DependencyInjection/Flows/OpenScope.php`
- `DependencyInjection/Flows/CloseScope.php`
- `DependencyInjection/Scopes/ScopeInterface.php`
- `DependencyInjection/Scopes/ManageScopes.php`
- `DependencyInjection/Scopes/ScopeStore.php`

## Behavior

- shared services live in global runtime storage
- scoped services live only in the current active scope
- transient services are never stored
- `flush()` and `reset()` clear both shared and scoped runtime storage as part of the clean-runtime boundary

## Important Rule

Scoped services fail closed when no scope is active.

The resolver will not silently store a scoped instance in shared storage.

## Public Usage

- `Container::openScope()`
- `Container::closeScope()`
- `Container::scopes()->withinScope(...)`

## Ownership Split

- flow entry files own entering and leaving scopes
- `ManageScopes` owns scope control
- `ScopeStore` owns actual stored instances
