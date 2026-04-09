# Worker And Request Lifecycle

This container distinguishes worker runtime state from request scope state.

## Request Boundary

Use one of these patterns:

- `openScope()` / `closeScope()`
- `scopes()->withinScope(...)`

Scoped services live only inside the active scope frame.

## Worker Boundary

Call `reset()` between jobs when the same process keeps running.

`reset()` clears:

- shared instances
- scoped instances
- lazy markers
- telemetry state
- callable caches
- attached compiled runtime handle

`reset()` keeps:

- authored registrations
- compiled artifacts on disk
- the ability to warm or reattach the compiled runtime again

## Benchmarks

The canonical lifecycle benchmarks are:

- `worker_cached_get`
- `request_lifecycle`

They prove both the steady-state worker loop and the explicit request scope boundary under the same Docker image and PHP settings.

## Regression Proof

Lifecycle correctness is defended by:

- [`../tests/Flows/CreateContainer/ContainerLifecycleSmokeTest.php`](../tests/Flows/CreateContainer/ContainerLifecycleSmokeTest.php)
- [`../tests/Flows/CreateContainer/WorkerRequestLifecycleSmokeTest.php`](../tests/Flows/CreateContainer/WorkerRequestLifecycleSmokeTest.php)
