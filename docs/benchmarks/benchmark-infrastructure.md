# Benchmark Infrastructure

## Overview

AvaX benchmark infrastructure measures framework performance, memory behavior, reliability, and overhead. It produces evidence files in `EVIDENCE/v5.5/` that serve as the basis for performance claims.

## Architecture

```
CLI / Script
    │
    ▼
tooling/benchmarks/v5_5_benchmark_runner.php
    │
    ├── Runs V5.5-01 through V5.5-10 stages
    │   (selectable via `php tooling/benchmarks/v5_5_benchmark_runner.php [stage|all]`)
    │
    ├── V5.5-05: Reference App Benchmarks
    │   Benchmarks all 13 reference apps from examples/v4/
    │   Each app is recreated per iteration with its routes
    │
    └── Uses RunBenchmark capability
            │
            ├── RunBenchmark::run()
            │   1. Warmup loop (excluded from results)
            │   2. Timed measurement loop
            │   3. Per-iteration microtime tracking
            │   4. Error counting (caught exceptions)
            │   5. Memory before/after/peak tracking
            │   6. Sorted times → percentiles (p50, p95, p99)
            │
            └── Produces BenchmarkResult
                    │
                    ├── toArray() — evidence serialization
                    ├── toCanonicalFormat() — environment-tagged format
                    └── Written to EVIDENCE/v5.5/*.json
```

## Components

### RunBenchmark

`framework/System/Capabilities/Benchmarks/RunBenchmark.php`

Core benchmark capability. Executes workloads with warmup, timing, and memory tracking.

```php
$runner = new RunBenchmark();
$result = $runner->run('name', callable $work, iterations: 1000, warmupIterations: 10);
```

### BenchmarkResult

`framework/System/Capabilities/Benchmarks/Foundation/BenchmarkResult.php`

Immutable readonly value object representing a single benchmark execution. Contains:

- **name**: benchmark identifier
- **iterations**: number of measured iterations
- **warmupIterations**: number of warmup iterations (excluded from results)
- **totalSeconds**: wall-clock time for measured iterations
- **avgMs, minMs, maxMs, p50Ms, p95Ms, p99Ms**: latency statistics
- **rps**: requests per second (iterations / totalSeconds)
- **memoryBeforeBytes, memoryAfterBytes, memoryPeakBytes**: memory measurements via `memory_get_usage(true)`
- **errorRate**: errors / iterations (0.0 to 1.0)
- **status**: GREEN, YELLOW, or RED
- **notes**: human-readable annotations

### BenchmarkSuite

`framework/System/Capabilities/Benchmarks/Foundation/BenchmarkSuite.php`

Aggregates multiple BenchmarkResults into a named suite with environment metadata.

## Methodology

### Warmup

Each benchmark runs a warmup loop before measurement. Warmup iterations are excluded from results. This eliminates first-run overhead (opcache compilation, autoloader initialization).

```php
$warmup = min($warmupIterations, $iterations);
for ($i = 0; $i < $warmup; $i++) { $work(); }
```

### Measurement

After warmup, each iteration is timed individually using `microtime(true)`. Times are stored in milliseconds.

```php
$start = microtime(true);
$work();
$times[] = (microtime(true) - $start) * 1000;
```

### Error Handling

If a workload throws, the error is caught, counted, and a 0.0ms time is recorded. This prevents one failing iteration from crashing the entire benchmark.

### Memory Tracking

Memory is measured using `memory_get_usage(true)`, which returns bytes allocated from the system (not just PHP's internal emalloc). Peak memory is tracked via `memory_get_peak_usage(true)`.

### Percentile Calculation

Times are sorted ascending. Percentiles are computed by index:

```
p50 = times[floor(count * 0.50)]
p95 = times[floor(count * 0.95)]
p99 = times[floor(count * 0.99)]
```

## Benchmark Stages

| Stage | Name | What It Measures |
|-------|------|-----------------|
| V5.5-01 | Environment Baseline | CPU, RAM, OS, PHP version, opcache, JIT |
| V5.5-02 | Microbenchmarks | Array ops, object creation, JSON roundtrip, hashing |
| V5.5-03 | Runtime Benchmarks | App creation, single request, parameter routing |
| V5.5-04 | HTTP Throughput | Hello, JSON API, parameter routing throughput |
| V5.5-05 | Reference App Benchmarks | All 13 reference apps from examples/v4/ |
| V5.5-06 | Soak Test | 10,000 iterations, memory growth, state isolation |
| V5.5-07 | Memory Leak Test | 5,000 iterations, memory classification |
| V5.5-08 | DB/Queue/Messaging | In-memory queue, JSON serialization |
| V5.5-09 | Observability & Security Overhead | Logging overhead, request signing overhead |
| V5.5-10 | Framework Comparison | AvaX vs raw PSR-15 baseline |

## Evidence Format

Each evidence file is valid JSON:

```json
{
  "stage": "V5.5-03",
  "type": "runtime-benchmark-results",
  "environment": {
    "php_version": "8.5.5",
    "opcache": "enabled",
    "jit": "disabled",
    "git_commit": "abc123...",
    "timestamp": "2026-05-11T17:00:00+00:00"
  },
  "results": [
    {
      "name": "single_request",
      "iterations": 500,
      "warmup_iterations": 50,
      "total_seconds": 0.0057,
      "metrics": {
        "avg_ms": 0.0114,
        "p50_ms": 0.011,
        "p95_ms": 0.015,
        "p99_ms": 0.0172,
        "min_ms": 0.0098,
        "max_ms": 0.0219,
        "rps": 87246.83,
        "memory_before_bytes": 10485760,
        "memory_after_bytes": 10485760,
        "memory_peak_bytes": 13107200,
        "error_rate": 0
      },
      "status": "GREEN",
      "notes": []
    }
  ]
}
```

### Status Classification

- **GREEN**: benchmark passed, no errors, memory within thresholds
- **YELLOW**: benchmark ran but with warnings (external dependency unavailable, memory growth > 1MB)
- **RED**: benchmark failed critical threshold (memory growth > 10MB, all errors)

### Unavailable Targets

When a benchmark cannot run (missing external dependency, stage lock), it is recorded as:

```json
{
  "name": "redis_queue",
  "status": "YELLOW",
  "notes": ["Redis not available"]
}
```

## Reference App Benchmarks (V5.5-05)

The 13 reference applications in `examples/v4/` are benchmarked by recreating their route structure and exercising representative request patterns:

| App | Routes Benchmarked | Complexity |
|-----|-------------------|------------|
| hello-world | GET /, GET /health | Minimal |
| url-shortener | POST /shorten, GET /{code} | Stateful closures |
| secure-registration-api | POST /register, GET /health | Minimal |
| parking-lot | GET /lots, POST /lots/{id}/park | Capacity policy |
| feature-flag-demo | GET /features, GET /features/{name}/check | Flag lookup |
| queue-worker-demo | POST /jobs, GET /jobs | Queue operations |
| webhook-receiver | POST /webhooks/{provider}, GET /inbox | Idempotency |
| observability-demo | GET /metrics, POST /events, GET /audit | Metrics + audit |
| outbox-messaging-demo | POST /orders, GET /outbox | Transactional outbox |
| file-upload-storage-demo | POST /upload, GET /files/{id} | Validation + storage |
| service-to-service-demo | POST /services/register, GET /services/{name}/resolve | Service registry |
| runtime-doctor-demo | GET /health, GET /health/ready, GET /doctor | Health checks |
| system-design-report-demo | GET /report, GET /capacity, GET /risks | Architecture reports |

Each reference app benchmark runs 200 iterations with 20 warmup iterations.

## Tooling Exception

The Benchmarks component uses a reduced canonical shape (no PublicSurface/, no Flows/) because it is tooling-scoped, not a production component. It does not expose a public API, handle HTTP requests, or define framework behavior. It exists solely to measure and produce evidence.

This is a documented, low-risk exception to the canonical component shape rules.

## How to Rerun Benchmarks

```bash
# Run all stages
php tooling/benchmarks/v5_5_benchmark_runner.php all

# Run a specific stage
php tooling/benchmarks/v5_5_benchmark_runner.php V5.5-05

# Run with environment variable
V55_STAGE=V5.5-03 php tooling/benchmarks/v5_5_benchmark_runner.php
```

## How to Verify Evidence

```bash
# Validate all JSON evidence files
for f in EVIDENCE/v5.5/*.json; do
  php -r "json_decode(file_get_contents('$f'), flags: JSON_THROW_ON_ERROR); echo '$f: valid\n';"
done

# Run benchmark infrastructure tests
vendor/bin/phpunit tests/Unit/Framework/Benchmarks/
```

## Environment Notes

- Memory values (`memory_before_bytes`, `memory_after_bytes`, `memory_peak_bytes`) are measured via `memory_get_usage(true)` which returns system-allocated memory in page-size increments
- Identical before/after values indicate no net memory growth, not absence of measurement
- Peak values may differ from before/after due to temporary allocations during execution
- `git_commit` is captured at runner startup time via `git rev-parse HEAD`
- If git is unavailable, `git_commit` will be `"unknown"`

## Unit Tests

- `tests/Unit/Framework/Benchmarks/BenchmarkResultTest.php` — 15 tests covering fromTimes, toArray, toCanonicalFormat, edge cases
- `tests/Unit/Framework/Benchmarks/RunBenchmarkTest.php` — 16 tests covering run, runSuite, error handling, warmup, memory, percentiles
- `tests/Unit/Framework/V4Benchmarks/V4BenchmarksTest.php` — 4 integration tests

Total: 35 benchmark infrastructure tests.
