# Plan: Concurrency PublicSurface Refactor

**Date:** 2026-05-08
**Parent:** 0000-concurrency-parallelism-boundary.md
**Stage:** Proposed
**Owner:** Operations/Concurrency
**Area:** Operations/Concurrency

## Goal

Refactor existing `Concurrency` component so that:

1. PublicSurface only delegates to Flows
2. Flows delegate to Capabilities (runtime backends)
3. Runtime-specific code stays in Capabilities
4. maxConcurrent parameter is properly implemented
5. Structured result types (ConcurrentResult, ConcurrentFailure)
6. Task keys/names preserved in results

## Current State (Problems)

```php
// Current Concurrency.php - PublicSurface does real work
public static function run(array $tasks, ?int $maxConcurrent = null) : array
{
    return self::all(tasks: $tasks);  // maxConcurrent ignored!
}

public static function all(array $tasks) : array
{
    $taskRunner = new TaskRunner();  // instantiates capability directly
    return $taskRunner->runAll($tasks);
}
```

Problems:

1. `run()` ignores `maxConcurrent`
2. PublicSurface instantiates `TaskRunner` directly
3. No structured result type
4. No failure model
5. Task keys lost (returns list, not array)
6. `runAll()` delegates to Fiber wrapper but maxConcurrent is ignored

## Target State

```php
// Target: PublicSurface only delegates
final readonly class Concurrency
{
    public static function run(
        array $tasks,
        int|null $maxConcurrent = null,
    ): ConcurrentResult {
        return (new RunConcurrentTasks())->run(
            tasks: $tasks,
            maxConcurrent: $maxConcurrent,
        );
    }

    public static function all(array $tasks): ConcurrentResult
    {
        return self::run(tasks: $tasks);
    }

    public static function race(array $tasks): mixed
    {
        return (new RaceTasks())->race(tasks: $tasks);
    }

    public static function start(callable $task): ConcurrentTask
    {
        return (new StartTask())->start(task: $task);
    }

    public static function await(ConcurrentTask $task): mixed
    {
        return (new WaitForTask())->await(task: $task);
    }
}
```

## Required Changes

### 1. Foundation Types

Create:

- `System/Foundation/ConcurrentResult.php` - structured result with values/failures
- `System/Foundation/ConcurrentFailure.php` - failure representation
- `System/Foundation/ConcurrentTask.php` - task handle for start/await
- `System/Foundation/TaskId.php`
- `System/Foundation/TaskDeadline.php`

### 2. Flow Implementation

Update:

- `System/Flows/RunConcurrentTasks/RunConcurrentTasks.php` - implement maxConcurrent
- `System/Flows/AwaitTask/AwaitTask.php` - implement properly with ConcurrentTask
- Create `System/Flows/RaceTasks/RaceTasks.php`
- Create `System/Flows/StartTask/StartTask.php`
- Create `System/Flows/WaitForTask/WaitForTask.php`
- Create `System/Flows/WaitForTasks/WaitForTasks.php`

### 3. Capability Layer

Keep existing but refactor:

- `System/Capabilities/Tasks/TaskRunner.php` → rename to `System/Capabilities/RunWithFibers/FiberTaskRunner.php`

Create new:

- `System/Capabilities/RunInCurrentProcess/CurrentProcessTaskRuntime.php` - sequential, deterministic
- `System/Capabilities/LimitRunningTasks/LimitRunningTasks.php`
- `System/Capabilities/CaptureTaskFailure/CaptureTaskFailure.php`
- `System/Capabilities/TrackRunningTasks/RunningTaskRegistry.php`
- `System/Capabilities/ChooseTaskRuntime/ChooseTaskRuntime.php`

### 4. PublicSurface Cleanup

Update `Concurrency.php`:

- Remove direct TaskRunner instantiation
- Remove business logic
- Only delegate to Flows
- Add proper PHPDoc with generics

### 5. Configuration

Create:

- `System/Configuration/ConcurrencyConfig.php`
- `System/Configuration/BuildConcurrencyRuntime.php`

## Files To Create

### Foundation (5 files)

- `System/Foundation/ConcurrentResult.php`
- `System/Foundation/ConcurrentFailure.php`
- `System/Foundation/ConcurrentTask.php`
- `System/Foundation/TaskId.php`
- `System/Foundation/TaskDeadline.php`

### Flows (5 files)

- `System/Flows/RaceTasks/RaceTasks.php`
- `System/Flows/StartTask/StartTask.php`
- `System/Flows/WaitForTask/WaitForTask.php`
- `System/Flows/WaitForTasks/WaitForTasks.php`

### Capabilities (6 files)

- `System/Capabilities/RunInCurrentProcess/CurrentProcessTaskRuntime.php`
- `System/Capabilities/LimitRunningTasks/LimitRunningTasks.php`
- `System/Capabilities/CaptureTaskFailure/CaptureTaskFailure.php`
- `System/Capabilities/TrackRunningTasks/RunningTaskRegistry.php`
- `System/Capabilities/ChooseTaskRuntime/ChooseTaskRuntime.php`
- `System/Capabilities/RunWithFibers/FiberTaskRuntime.php` (rename from TaskRunner)

### Configuration (2 files)

- `System/Configuration/ConcurrencyConfig.php`
- `System/Configuration/BuildConcurrencyRuntime.php`

## Files To Modify

- `System/PublicSurface/Concurrency.php` - refactor to delegate only
- `System/Flows/RunConcurrentTasks/RunConcurrentTasks.php` - implement maxConcurrent
- `System/Flows/AwaitTask/AwaitTask.php` - implement properly

## Files To Delete

- `System/Capabilities/Tasks/TaskRunner.php` (moved to FiberTaskRuntime)
- `System/Capabilities/Tasks/` folder (empty after move)
- `System/Capabilities/EventLoopAdapters/SynchronousEventLoop.php` (merged into CurrentProcess runtime)
- `System/Capabilities/EventLoopAdapters/` folder (empty after merge)

## Public API (Final)

```php
namespace Avax\Components\Operations\Concurrency\System\PublicSurface;

final readonly class Concurrency
{
    /**
     * Run multiple tasks concurrently with optional concurrency limit.
     *
     * @param array<string|int, callable(): mixed> $tasks
     */
    public static function run(
        array $tasks,
        int|null $maxConcurrent = null,
    ): ConcurrentResult;

    /**
     * Run multiple tasks concurrently and wait for all.
     *
     * @param array<string|int, callable(): mixed> $tasks
     */
    public static function all(array $tasks): ConcurrentResult;

    /**
     * Run multiple tasks and return the first successful result.
     *
     * @param list<callable(): mixed> $tasks
     */
    public static function race(array $tasks): mixed;

    /**
     * Start a task without waiting for result.
     *
     * @template TResult
     * @param callable(): TResult $task
     */
    public static function start(callable $task): ConcurrentTask;

    /**
     * Await a previously started task.
     */
    public static function await(ConcurrentTask $task): mixed;
}
```

## ConcurrentResult Type

```php
final readonly class ConcurrentResult
{
    public function __construct(
        public array $values,        // named task results
        public array $failures,       // ConcurrentFailure[]
        public int $startedTasks,
        public int $finishedTasks,
        public int $failedTasks,
        public int $cancelledTasks,
    ) {}

    public function value(string|int $name): mixed;
    public function values(): array;
    public function successful(): bool;
    public function throwIfFailed(): void;
}
```

## Validation Commands

```bash
composer dump-autoload -o
vendor/bin/phpunit tests/Unit/Components/Operations/Concurrency/
vendor/bin/phpstan analyse components/Operations/Concurrency --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-public-surface.php components/Operations/Concurrency
```

## Risks

1. Breaking change for existing Concurrency users (returns ConcurrentResult, not array)
2. Fiber generics PHPStan issues
3. Backward compatibility with existing calls

## Mitigation

1. Add `@deprecated` notice with migration path
2. Provide BC layer via `Concurrency::all()->values()` returning old array format
3. Document migration in CHANGELOG

## Dependencies

- Phase 1: Complete plan and ADR
- Phase 2: Parallelism component foundation (0001-parallelism-component-impl.md)
- Phase 3: This refactor

## Notes

This refactor must complete BEFORE adding Amp/Swoole runtimes.
The runtime abstraction must be solid first.
