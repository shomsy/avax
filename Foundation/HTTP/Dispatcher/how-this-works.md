# Dispatcher - how this works

`Dispatcher/*` owns controller invocation once the router has already decided which action should run.

- `ControllerDispatcher` resolves callables, controller-method pairs, and invokable controllers.
- It is intentionally downstream of routing and upstream of response creation.
- It should not grow its own routing or middleware concerns.
