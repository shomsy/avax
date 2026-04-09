# Scopes

Scopes are the isolation boundary for scoped services.

## Main Files

- `src/Flows/OpenScope/OpenScope.php`
- `src/Flows/CloseScope/CloseScope.php`
- `src/Capabilities/Runtime/Scopes/ScopeInterface.php`
- `src/Capabilities/Runtime/Scopes/ManageScopes.php`
- `src/Capabilities/Runtime/Scopes/ScopeStore.php`

## Behavior

- shared services live in global runtime storage
- scoped services live only in the current active scope
- transient services are never stored
- `flush()` clears shared and scoped runtime storage, derived caches, and compiled artifacts without removing canonical
  registrations
- `reset()` clears shared and scoped runtime storage plus other disposable runtime state while keeping registrations and
  compiled artifacts

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
