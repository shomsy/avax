# How This Works: Queue Component

## What This Component Does

The Queue component provides asynchronous and synchronous job processing for AvaX applications. It handles:

- **Job dispatching** — send jobs to a queue for later or immediate processing
- **Worker loop** — long-running process that polls queues and executes jobs
- **Batch tracking** — dispatch and monitor groups of related jobs
- **Task abstractions** — higher-level dispatch strategies (async, deferred, sync)
- **Multiple queue backends** — in-memory, synchronous, array-based, and Redis (skeleton)
- **Dead-letter handling** — capture permanently failed jobs after max attempts
- **Job lifecycle** — identification, execution, result capture, and failure tracking

## What This Component Does NOT Do

- CLI commands for worker management (no artisan-style `queue:work`, `queue:restart`)
- Full Redis driver implementation (RedisQueue is a skeleton)
- Job prioritization or multiple queue priority levels
- Delayed job scheduling beyond simple delay dispatch
- Job chaining or dependency graphs
- Horizon-style dashboard or monitoring UI
- Distributed locking across workers
- Job middleware pipelines

## Public API

### Dispatcher (`PublicSurface/Dispatcher.php`)

| Method | Description |
|---|---|
| `dispatch($job)` | Dispatch a job to the configured queue |
| `dispatchSync($job)` | Dispatch and execute a job immediately (synchronous) |
| `dispatchBatch($jobs)` | Dispatch a batch of jobs with collective tracking |
| `dispatchAfter($job, $delay)` | Schedule a job to run after a specified delay |

### QueueWorker (`PublicSurface/QueueWorker.php`)

| Method | Description |
|---|---|
| `run($queue, $options)` | Start the worker loop for a given queue with options |
| `stop()` | Signal the worker to stop gracefully |

### Tasks (`PublicSurface/Tasks.php`)

Task dispatch facade for higher-level dispatching patterns.

### TaskBatch (`PublicSurface/TaskBatch.php`)

Batch task tracking — monitor progress and results of job batches.

### JobId (`PublicSurface/JobId.php`)

Value object representing a unique job identifier.

### JobResult (`PublicSurface/JobResult.php`)

Value object capturing the result of a job execution (success/failure, output, metadata).

## Internal Flow

### Capabilities

| Area | Files | Responsibility |
|---|---|---|
| **Job** | `JobInterface`, `JobDefinition`, `JobHandler`, `JobRegistry` | Job contract, definition, handler resolution, and registry |
| **Queue** | `Queue` interface, `MemoryQueue`, `SyncQueue`, `ArrayQueue`, `RedisQueue`, `QueueBroker` | Queue storage implementations and broker routing |
| **Driver** | `QueueDriverInterface`, `SyncDriver` | Driver abstraction and synchronous driver implementation |
| **TaskDispatch** | `AsyncDispatcher`, `DeferredDispatcher`, `SyncDispatcher`, `TaskDispatchers`, `DispatchStrategyResolver` | Dispatch strategy selection and execution |
| **TaskBatch** | Batch tracking | Track and report on batches of dispatched jobs |

### Flows

| Flow | Description |
|---|---|
| **DispatchJob** | Receive a job, resolve the target queue, serialize payload, enqueue |
| **ProcessJob** | Dequeue a job, resolve handler, execute with retry/timeout, capture result |
| **RunWorkerLoop** | Poll queue, process available jobs, handle signals, respect memory limits, repeat |

### Foundation

| File | Responsibility |
|---|---|
| `JobFailed` | Exception for job execution failures |
| `QueueConnectionNotFound` | Exception when a requested queue connection does not exist |
| `MaxAttemptsExceeded` | Exception when a job exceeds its maximum retry attempts |
| **Dead-letter handling** | Permanently failed jobs are routed to a dead-letter queue for inspection |

## Dependencies

| Component | Usage |
|---|---|
| **Resilience** | Retry logic with backoff, timeout enforcement for job processing |
| **Observability** | Metrics and tracing for job lifecycle (dispatch, start, complete, fail) |
| **DataTransfer** | Job payload serialization and deserialization |

## Failure Behavior

| Scenario | Behavior |
|---|---|
| Job throws exception | Retry with backoff (resilience component), up to max attempts |
| Max attempts exceeded | Job moved to dead-letter queue, `MaxAttemptsExceeded` thrown |
| Worker receives SIGTERM | Graceful shutdown — finish current job, then stop |
| Memory limit reached | Worker stops to prevent leak accumulation |
| Queue connection missing | `QueueConnectionNotFound` thrown at dispatch time |
| Batch partial failure | Individual job failures tracked; batch reports partial completion |

## Runtime Safety

| Guard | Default | Description |
|---|---|---|
| Max attempts | 3 | Maximum retries before dead-letter |
| Job timeout | Configurable | Enforced via Resilience component |
| Memory limit | Configurable | Worker stops when limit reached |
| Queue connection validation | Required | Connection must exist before dispatch |

## Examples

### Dispatch a Job

```php
use AvaX\Operations\Queue\System\PublicSurface\Dispatcher;

$dispatcher = new Dispatcher();
$dispatcher->dispatch(new SendEmail($user, $message));
```

### Dispatch Synchronously

```php
$dispatcher->dispatchSync(new GenerateReport($criteria));
```

### Dispatch a Batch

```php
$dispatcher->dispatchBatch([
    new SendEmail($user1, $message),
    new SendEmail($user2, $message),
    new SendEmail($user3, $message),
]);
```

### Run a Worker

```php
use AvaX\Operations\Queue\System\PublicSurface\QueueWorker;

$worker = new QueueWorker();
$worker->run('default', [
    'memory' => 128,
    'timeout' => 60,
    'maxAttempts' => 3,
]);
```

### Graceful Stop

```php
$worker->stop();
```

## Known Limits

| Limit | Status | Notes |
|---|---|---|
| CLI worker commands | Missing | No `queue:work`, `queue:restart`, etc. |
| Redis driver | Skeleton | `RedisQueue` exists but lacks full driver implementation |
| Job prioritization | Missing | No priority queues or priority ordering |
| Delayed scheduling | Basic | Simple delay only; no cron-style or complex scheduling |
| Job chaining | Missing | No dependency graph or chained job execution |
| Dashboard | Missing | No Horizon-style monitoring UI |
| Distributed locking | Missing | No cross-worker coordination |
| Job middleware | Missing | No middleware pipeline for jobs |

## Current Status

**Status: YELLOW**

| Aspect | State |
|---|---|
| Dispatcher | Present — dispatch, dispatchSync, dispatchBatch, dispatchAfter |
| Worker loop | Present — poll, process, signal handling |
| MemoryQueue | Present — in-memory queue for testing |
| SyncQueue | Present — synchronous execution |
| Job registry | Present — handler resolution |
| Dead-letter | Present — failed job capture |
| Tests | 73 tests in `tests/Unit/Components/Operations/Queue/` |
| CLI commands | Missing |
| Redis driver | Skeleton |
| Job prioritization | Missing |
| Documentation | Minimal |
