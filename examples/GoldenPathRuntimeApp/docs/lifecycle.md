# Runtime Lifecycle

## HTTP Request Lifecycle

Each HTTP request follows this lifecycle:

```
Request → Open Scope → Handle → Close Scope → Reset State
```

### 1. Boot

`Avax::boot(ApplicationBuilder)` initializes:

- `RuntimeState` — tracks booted status, boot count, timestamps
- `RuntimeContext` — per-request context management
- `RequestScopeStore` — request-scoped data storage
- `StateResetRegistry` — registers all resettable components
- `ComponentRegistry` — lazy singleton component factory
- `HandleIncomingHttp` — full request lifecycle flow

### 2. Handle Request

`$avax->http()->handle(RuntimeRequest)` triggers:

1. **OpenHttpRequestScope** — opens a new request scope, starts request in context
2. **Invoke HTTP handler** — routes file callable receives RouterInterface, matches URI, executes action
3. **Normalize response** — arrays/objects → JSON, strings → text, PSR-7 → RuntimeResponse
4. **Finish request** — records result in RuntimeContext
5. **CloseHttpRequestScope** (finally) — always closes scope, even on error
6. **Return RuntimeResponse** — status code, headers, body

### 3. Reset State

`$avax->resetState()` calls `StateResetRegistry::resetAll()`:

- Resets all registered `ResettableState` components
- Returns `StateResetReport` with successes and failures
- Ensures clean state between requests

## Worker Loop Lifecycle

Long-lived workers process requests via `Runtime::runWorker(WorkerRuntimeInterface)`:

```
start → receive → handle → reset → receive → handle → reset → ... → stop → reset all → shutdown
```

### Per-Request Cycle

1. **WorkerRuntime::receive()** — fetches next `WorkerRequest` (returns null when empty)
2. **HandleIncomingHttp::handle()** — full request lifecycle (open scope, handle, close scope)
3. **WorkerRuntime::send()** — delivers `WorkerResponse` back to worker
4. **StateResetRegistry::resetAll()** — resets state between each request
5. Repeat until receive returns null

### Shutdown Cycle

1. **WorkerLoop::stop()** — sets running flag to false
2. **WorkerRuntime::stop()** — signals worker runtime to stop
3. **StateResetRegistry::resetAll()** — final full state reset
4. **RuntimeState::markShutdown()** — records shutdown timestamp
5. **WorkerLifecycle::stop()** — records worker stopped timestamp

### Key Properties

- **State isolation**: Each request gets its own scope — no cross-request data leaks
- **Automatic cleanup**: Scope closes in `finally` block — even on exception
- **Per-request reset**: StateResetRegistry runs between every request in the worker loop
- **Final reset**: Full reset runs again at shutdown for clean teardown
- **No hidden I/O**: Reset is pure state management — no network or filesystem calls
