# Concurrency — How This Works

## What This Component Does

The Concurrency component provides **cooperative task concurrency** within a single PHP process using PHP Fibers.

It is NOT process-based parallelism. It does NOT create OS processes. It does NOT provide CPU-level parallelism.

## Concurrency vs Parallelism

| Aspect | Concurrency | Parallelism |
|--------|------------|-------------|
| Model | Cooperative fibers | Separate OS processes |
| CPU | Single-threaded | Multiple processes |
| Isolation | Shared memory | Process isolation |
| Use case | I/O-bound, cooperative tasks | CPU-bound, blocking work |
| Runtime | `FiberTaskRuntime` | `SymfonyProcessParallelRuntime` |
| Fallback | `CurrentProcessTaskRuntime` | `CurrentProcessParallelRuntime` |

**Use Concurrency when:**
- Tasks perform I/O (HTTP calls, database queries, file reads)
- Tasks can explicitly yield/suspend at safe points
- You need many tasks managed in a single process
- Memory efficiency matters

**Use Parallelism when:**
- Tasks are CPU-bound
- Tasks perform blocking operations that cannot yield
- You need true isolation between workers
- Process-level fault tolerance matters

## Cooperative Concurrency Limitation

**This is the most important fact about this component:**

> Concurrency in this runtime is cooperative.
> Tasks must explicitly yield/suspend at safe points for interleaving to occur.
> CPU-bound blocking work belongs to Parallelism, not Fiber concurrency.

If a task closure runs to completion without calling `Fiber::suspend()`, it executes sequentially within its fiber. The round-robin scheduler exists but cannot interleave tasks that never yield.

### What this means in practice

```php
// Sequential — no interleaving occurs
$runtime->run([
    'A' => fn() => { /* does work, returns */ },
    'B' => fn() => { /* does work, returns */ },
]);
// Result: A runs, then B runs. Sequential.

// Cooperative — interleaving occurs
$runtime->run([
    'A' => fn() => { /* work */; Fiber::suspend(); /* more work */; Fiber::suspend(); /* done */ },
    'B' => fn() => { /* work */; Fiber::suspend(); /* more work */; Fiber::suspend(); /* done */ },
]);
// Result: A:start, B:start, A:middle, B:middle, A:end, B:end. Interleaved.
```

### When does interleaving happen?

Interleaving happens when tasks call `Fiber::suspend()`. This is the cooperative yield point.

Typical use: wrapping I/O operations in suspending adapters:

```php
'fetch_user' => fn() => $httpClient->getAsync($url)->await(), // await() calls Fiber::suspend()
'fetch_order' => fn() => $db->queryAsync($sql)->await(),       // await() calls Fiber::suspend()
```

### race() behavior

`race()` starts all fibers and returns the first non-null result. It does NOT cancel remaining fibers — they are abandoned when the method returns. This is cooperative concurrency: there is no forced cancellation mechanism.

### maxConcurrent behavior

`maxConcurrent` limits how many fibers are active in a single batch. Tasks beyond the limit are queued and started after the current batch completes. This prevents resource exhaustion when running many concurrent tasks.

## Runtime Selection

`BuildConcurrencyRuntime` auto-detects available runtimes:

1. **Fiber runtime** (`FiberTaskRuntime`) — selected when `class_exists(Fiber::class)` (PHP 8.1+)
2. **Current process runtime** (`CurrentProcessTaskRuntime`) — sequential fallback

Default: `fiber` when available, `current_process` otherwise.

## Failure Handling

Failures are captured per-task and returned in `ConcurrentResult`. Exceptions are NOT swallowed. `throwIfFailed()` throws `ConcurrencyException` with the first failure's message.

## Security

This component runs entirely in-process. There is no serialization, no IPC, no external processes. All task closures execute in the same memory space.

## DX Shortcuts: async() / await()

The `Avax\` namespace provides two convenience functions that wrap the Concurrency public surface:

```php
use function Avax\async;
use function Avax\await;

// Start a task — returns ConcurrentTask
$task = async(fn() => doSomething());

// Wait for a task — returns the result
$result = await($task);

// Shortcut: await accepts a Closure directly
$result = await(fn() => doSomething());
```

### How they work

`async()` delegates to `Concurrency::start()`. `await()` delegates to `Concurrency::await()`. They are thin DX wrappers — they do not change the concurrency model.

When `await()` receives a `Closure`, it calls `Concurrency::start()` internally then immediately awaits. This is convenience syntax for fire-and-wait-single-task.

### Cooperative concurrency limitation still applies

These shortcuts do not magically make blocking work concurrent. CPU-bound or blocking closures passed to `async()` execute in the same fiber scheduler:

```php
// This does NOT run in parallel — both execute sequentially in fibers
$taskA = async(fn() => heavyComputation()); // blocks fiber
$taskB = async(fn() => moreComputation());   // blocks fiber
await($taskA); // waits for A, B is still idle
await($taskB);
```

For true parallelism of CPU-bound or blocking work, use the **Parallelism** component:

```php
use Avax\Components\Operations\Parallelism\System\PublicSurface\Parallelism;

$result = Parallelism::run([
    'A' => fn() => heavyComputation(),
    'B' => fn() => moreComputation(),
])->withMaxWorkers(2);
```

### When to use async/await vs Parallelism

| Pattern | Use case | Example |
|---------|----------|---------|
| `async()` + `await()` | Multiple I/O tasks started before waiting | Start 3 HTTP requests, then await all |
| `await(fn() => ...)` | Single task convenience | `await(fn() => fetchUser($id))` |
| Parallelism | CPU-bound, blocking, isolated work | `Parallelism::run([...])` |

## Remaining Risks

- No automatic cancellation of remaining tasks in `race()`
- No deadline/timeout enforcement (TaskDeadline exists but is not wired into the fiber scheduler)
- Tasks that block (e.g., `sleep()`, synchronous I/O) block the entire scheduler
- No built-in async I/O adapters — tasks must provide their own suspension points
