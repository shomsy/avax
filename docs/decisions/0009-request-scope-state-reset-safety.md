# ADR 0009: Request Scope and State Reset Safety

## Status

Accepted

## Context

Long-lived runtimes (RoadRunner, FrankenPHP, Swoole, Workerman) can leak request state between requests if state is stored globally.

## Decision

Every request-scoped value must live inside `RequestScope`. The `RequestScopeStore` manages open/close lifecycle. `StateResetRegistry` ensures all state is cleared between worker requests.

## Consequences

- `RequestScope` prevents accidental global state storage
- `RequestScopeStore` enforces one-active-scope-at-a-time
- `StateResetRegistry` resets all registered state after each worker request
- Tests must prove no state leaks between simulated requests

## Done Criteria

1. Two simulated requests cannot see each other's scoped values
2. Current request is never stored globally
3. Current user/session/route/correlation id live in request scope
4. `ResetApplicationState` is called in worker request path
5. Tests prove no state leak