# MemoryGuard — How This Works

## Purpose

MemoryGuard monitors worker memory across requests in long-lived runtimes (ReactPHP, RoadRunner, Swoole, FrankenPHP).
It detects memory growth that could indicate leaks and requests graceful worker recycle when thresholds are exceeded.

## Components

### RecordMemorySnapshot
Records current memory usage and peak usage. Returns a typed array with `memory_bytes`, `memory_mb`, `peak_bytes`, `peak_mb`.

### CheckMemoryThreshold
Checks current memory against a configurable soft threshold. Supports both live check and snapshot-based check.

### MonitorWorkerMemory
Full memory tracking lifecycle:
1. `captureBefore()` — records memory before request handling
2. `captureAfter()` — records memory after, increments request count, evaluates thresholds
3. `memoryDelta()` — calculates bytes changed between before/after
4. `peakMemory()` — tracks highest memory seen across all requests
5. `latestSnapshot()` — returns the most recent complete snapshot
6. `recycleDecision()` — returns a `RequestWorkerRecycle` if any threshold was exceeded

### CalculateMemoryGrowthRate
Uses linear regression on memory snapshots to calculate growth rate in bytes per request.
`isPotentialLeak()` determines if growth rate exceeds a configurable threshold.

### RequestWorkerRecycle
A decision/finding object (NOT an actual process restart):
- `RecycleReason::SoftThresholdExceeded` — memory above soft limit
- `RecycleReason::HardThresholdExceeded` — memory above hard limit
- `RecycleReason::MaxRequestsExceeded` — request count reached maximum

## Threshold Configuration

```php
$monitor = new MonitorWorkerMemory(
    softThresholdBytes: 128 * 1024 * 1024,  // 128MB — warning
    hardThresholdBytes: 256 * 1024 * 1024,  // 256MB — urgent recycle
    maxRequests: 1000,                       // 0 = unlimited
);
```

## Safety Guarantees

- MemoryGuard never terminates a request mid-flight
- It only records decisions that a supervisor can act on
- Real process restart is handled by the runtime supervisor (V4-17)
- `clearRecycleDecision()` must be called after the decision is acted upon

## Integration with ReactPHP

```php
$memoryGuard = new MonitorWorkerMemory();
$runtime = new RunReactHttpServer();
$runtime->setMemoryGuard($memoryGuard);
$runtime->startWarmSmoke($handler);
// Memory before/after captured, snapshot available via $memoryGuard->latestSnapshot()
```
