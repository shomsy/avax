# Plan: Parallelism Component Implementation

**Date:** 2026-05-08
**Parent:** 0000-concurrency-parallelism-boundary.md
**Stage:** Proposed
**Owner:** Operations/Parallelism
**Area:** Operations/Parallelism

## Goal

Create `components/Operations/Parallelism/` with:

- Canonical AvaX component shape (System/PublicSurface, Flows, Capabilities, Configuration, Foundation)
- CurrentProcess runtime as deterministic fallback for tests/unsupported environments
- Symfony Process as the default real process-pool runtime
- No runtime-specific leakage into PublicSurface

## NOT In Scope This Pass

- Amp runtime
- Swoole runtime
- RoadRunner/FrankenPHP integration
- PCNTL/fork-based parallelism
- FFI shared memory
- Queue integration
- Scheduler integration

## Structure

```
components/Operations/Parallelism/
  System/
    PublicSurface/
      Parallel.php          # Public facade - only delegates
      ParallelResult.php    # Structured result with values/failures
      ParallelFailure.php   # Task failure representation
      ParallelWork.php      # Serializable work unit

    Flows/
      RunWorkInParallel/
        RunWorkInParallel.php
      MapItemsInParallel/
        MapItemsInParallel.php

    Capabilities/
      RunInCurrentProcess/
        CurrentProcessParallelRuntime.php
      RunThroughProcessPool/
        SymfonyProcessParallelRuntime.php
        StartWorkerProcess.php
        ReadWorkerResult.php
        StopWorkerProcess.php
      SerializeWorkPayload/
        SerializeWorkPayload.php
        DeserializeWorkPayload.php
        RejectUnserializableWork.php
      LimitWorkerCount/
        LimitWorkerCount.php
      CaptureWorkerFailure/
        CaptureWorkerFailure.php

    Configuration/
      ParallelismConfig.php
      BuildParallelRuntime.php

    Foundation/
      WorkerId.php
      WorkerResult.php
```

## Public API

```php
namespace Avax\Components\Operations\Parallelism\System\PublicSurface;

final readonly class Parallel
{
    public static function run(
        array $work,
        int|null $maxWorkers = null,
    ): ParallelResult;

    public static function map(
        iterable $items,
        callable|string $using,
        int|null $maxWorkers = null,
    ): ParallelResult;
}
```

## Result Types

```php
final readonly class ParallelResult
{
    public function __construct(
        public array $values,        // named results
        public array $failures,       // ParallelFailure[]
        public int $startedWorkers,
        public int $finishedWorkers,
        public int $failedWorkers,
    ) {}

    public function value(string|int $name): mixed;
    public function successful(): bool;
    public function throwIfFailed(): void;
}

final readonly class ParallelFailure
{
    public function __construct(
        public string|int $name,
        public string $message,
        public int $code,
        public ?\Throwable $previous,
    ) {}
}
```

## Implementation Phases

### Phase 1: Foundation

1. Create directory structure
2. Create `ParallelWork.php` - serializable work unit
3. Create `ParallelResult.php` - structured result
4. Create `ParallelFailure.php` - failure representation
5. Create `Foundation/WorkerId.php`
6. Create `Foundation/WorkerResult.php`

### Phase 2: CurrentProcess Runtime

1. Create `Capabilities/RunInCurrentProcess/CurrentProcessParallelRuntime.php`
2. Create `Capabilities/LimitWorkerCount/LimitWorkerCount.php`
3. Create `Capabilities/CaptureWorkerFailure/CaptureWorkerFailure.php`
4. Create `Flows/RunWorkInParallel/RunWorkInParallel.php`
5. Wire into `PublicSurface/Parallel.php`
6. Write tests for CurrentProcess runtime

### Phase 3: Symfony Process Runtime

1. Add `symfony/process` to composer.json (require, not suggest - for tests)
2. Create `Capabilities/SerializeWorkPayload/SerializeWorkPayload.php`
3. Create `Capabilities/SerializeWorkPayload/DeserializeWorkPayload.php`
4. Create `Capabilities/SerializeWorkPayload/RejectUnserializableWork.php`
5. Create `Capabilities/RunThroughProcessPool/StartWorkerProcess.php`
6. Create `Capabilities/RunThroughProcessPool/ReadWorkerResult.php`
7. Create `Capabilities/RunThroughProcessPool/StopWorkerProcess.php`
8. Create `Capabilities/RunThroughProcessPool/SymfonyProcessParallelRuntime.php`
9. Create `Flows/MapItemsInParallel/MapItemsInParallel.php`
10. Wire into `PublicSurface/Parallel.php`
11. Write tests for Process runtime

### Phase 4: Configuration

1. Create `Configuration/ParallelismConfig.php`
2. Create `Configuration/BuildParallelRuntime.php`
3. Create `docs/decisions/0008-operations-parallelism-runtime-boundary.md`

### Phase 5: Validation

1. Run `composer dump-autoload -o`
2. Run `vendor/bin/phpunit` relevant tests
3. Run `vendor/bin/phpstan analyse` relevant paths
4. Run architecture tests (no Symfony Process in PublicSurface)
5. Check forbidden folder names

## Files To Create

### Foundation (4 files)

- `System/Foundation/WorkerId.php`
- `System/Foundation/WorkerResult.php`
- `System/Foundation/ParallelResult.php` (moved from PublicSurface)
- `System/Foundation/ParallelFailure.php` (moved from PublicSurface)

### PublicSurface (3 files)

- `System/PublicSurface/ParallelWork.php`
- `System/PublicSurface/Parallel.php` (refactor from existing)

### Flows (2 files)

- `System/Flows/RunWorkInParallel/RunWorkInParallel.php`
- `System/Flows/MapItemsInParallel/MapItemsInParallel.php`

### Capabilities (8 files)

- `System/Capabilities/RunInCurrentProcess/CurrentProcessParallelRuntime.php`
- `System/Capabilities/LimitWorkerCount/LimitWorkerCount.php`
- `System/Capabilities/CaptureWorkerFailure/CaptureWorkerFailure.php`
- `System/Capabilities/SerializeWorkPayload/SerializeWorkPayload.php`
- `System/Capabilities/SerializeWorkPayload/DeserializeWorkPayload.php`
- `System/Capabilities/SerializeWorkPayload/RejectUnserializableWork.php`
- `System/Capabilities/RunThroughProcessPool/SymfonyProcessParallelRuntime.php`
- `System/Capabilities/RunThroughProcessPool/StartWorkerProcess.php`
- `System/Capabilities/RunThroughProcessPool/ReadWorkerResult.php`
- `System/Capabilities/RunThroughProcessPool/StopWorkerProcess.php`

### Configuration (2 files)

- `System/Configuration/ParallelismConfig.php`
- `System/Configuration/BuildParallelRuntime.php`

### Documentation (1 file)

- `docs/decisions/0008-operations-parallelism-runtime-boundary.md`

### Tests (TBD - in central test tree)

- Tests for all flows

## Files To Modify

- `composer.json` - add symfony/process
- `phpstan-baseline.neon` - add baselines if needed

## Validation Commands

```bash
composer dump-autoload -o
vendor/bin/phpunit tests/Unit/Components/Operations/Parallelism/
vendor/bin/phpstan analyse components/Operations/Parallelism --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-forbidden-folders.php components/Operations/Parallelism
```

## Risks

1. Symfony Process serialization limitations for closures
2. Process pool lifecycle management
3. Worker process cleanup on failure
4. Memory growth with long-running pools
5. Serialization of complex work payloads

## Mitigation

1. Reject unserializable work early
2. Use explicit ParallelWork action class
3. Limit worker count from start
4. Implement proper process cleanup on timeout/cancellation
5. Document serialization requirements clearly
