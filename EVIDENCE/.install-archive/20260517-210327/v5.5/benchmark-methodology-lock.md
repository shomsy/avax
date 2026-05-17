# V5.5-00: Benchmark Methodology Lock

**Date:** 2026-05-11
**Status:** GREEN

## Purpose

This document locks the benchmark methodology for V5.5. No benchmark numbers may be produced until this methodology is locked. All subsequent V5.5 stages must follow these rules.

## Methodology Rules

### 1. Same Machine Rule
All benchmarks for comparison must run on the same physical or virtual machine. Cross-machine comparisons are invalid.

### 2. Same PHP Version Rule
All benchmarks must record PHP version. Comparisons across PHP versions must note the version difference.

### 3. Opcache/JIT Configuration Rule
All benchmarks must record opcache and JIT configuration:
- `opcache.enable`
- `opcache.jit`
- `opcache.jit_buffer_size`
- `opcache.memory_consumption`

Benchmark profiles:
- **CLI default**: opcache disabled (PHP CLI default)
- **Production CLI**: opcache enabled, JIT disabled
- **Production JIT**: opcache enabled, JIT enabled (1255 or similar)

### 4. Same Request Body Rule
HTTP benchmarks must use identical request bodies for equivalent endpoints.

### 5. Same Response Body Rule
HTTP benchmarks must use identical response bodies for equivalent endpoints.

### 6. Same Database Scenario Rule
Database benchmarks must use the same schema, data volume, and query patterns. SQLite in-memory for local proof; production DB for external comparison only when available.

### 7. Same Concurrency Rule
HTTP throughput benchmarks must record and use the same concurrency level. Default: 1 (sequential), then 10, 50, 100 for scaling profile.

### 8. Warm/Cold Distinction
- **Cold**: first execution after process start, no prior warmup
- **Warm**: after configured warmup iterations
- Both must be reported separately where meaningful
- Warmup iterations are excluded from measured results

### 9. Iterations
- **Microbenchmarks**: warmup=50, measured=1000
- **Runtime benchmarks**: warmup=100, measured=5000
- **Soak tests**: warmup=100, measured=10000 (100k if feasible)
- **HTTP benchmarks**: warmup=100 requests, measured=5000 requests
- Individual benchmarks may override with documented reason

### 10. Reporting Format
Canonical JSON output shape:

```json
{
  "stage": "V5.5-XX",
  "benchmark": "...",
  "environment_id": "...",
  "timestamp": "...",
  "git_commit": "...",
  "php_version": "...",
  "opcache": "...",
  "jit": "...",
  "runtime": "...",
  "iterations": 0,
  "warmup_iterations": 0,
  "concurrency": 0,
  "metrics": {
    "avg_ms": 0,
    "p50_ms": 0,
    "p95_ms": 0,
    "p99_ms": 0,
    "min_ms": 0,
    "max_ms": 0,
    "rps": 0,
    "memory_before_bytes": 0,
    "memory_after_bytes": 0,
    "memory_peak_bytes": 0,
    "error_rate": 0
  },
  "status": "GREEN|YELLOW|RED",
  "notes": []
}
```

### 11. Outlier Handling
- P99 reported alongside P95
- Max reported for anomaly detection
- Outliers are not discarded; they are reported
- If outlier rate > 5%, note as YELLOW with investigation recommendation

### 12. Warmup Iterations
- Warmup iterations are always excluded from measured results
- Warmup count is always recorded in output
- Warmup must be sufficient for JIT to stabilize if JIT is enabled

### 13. Measured Iterations
- Only measured iterations contribute to avg/p50/p95/p99
- Iteration count is always recorded in output

### 14. Repetitions
- Each benchmark run is one execution
- For statistical confidence, run benchmark command 3 times and compare
- If results vary > 10% between runs, note as YELLOW

### 15. Clock Source
- `microtime(true)` for PHP-level timing
- Sufficient for microsecond-level accuracy
- No external clock synchronization required for local benchmarks

### 16. Memory Metric Source
- `memory_get_usage()` for current memory
- `memory_get_peak_usage()` for peak memory
- Measured before and after benchmark loop
- Real memory = `memory_get_usage(true)` (actual allocated)
- Emalloc memory = `memory_get_usage(false)` (PHP emalloc)
- Both reported where meaningful

### 17. CPU Metric Source
- Not available portably in PHP
- If `/proc/self/stat` or similar is available, record CPU ticks
- Otherwise, report as unavailable

### 18. Confidence Notes
- Results are point-in-time measurements
- Confidence requires 3+ runs with < 10% variance
- Single run is proof-of-execution, not statistical proof

### 19. Comparison Fairness Rules
- Same machine, PHP version, opcache/JIT config, request/response body, concurrency, iterations
- Framework comparison must implement equivalent functionality
- "Hello world" vs "hello world" is fair
- "Hello world" vs "full DI boot" is not fair — must compare equivalent boot levels

### 20. Unavailable Target Reporting Rules
- If a benchmark target is not available, report as `NOT_AVAILABLE_WITH_REASON`
- Do not fake benchmark numbers for unavailable targets
- Do not claim a target was benchmarked if it was not
- Example: `RoadRunner: NOT_AVAILABLE — adapter is ROADMAP, not implemented`

## Governance Compliance Matrix

| Governance Document | Applies? | Rules Applied | Violations Found | Fixes Made | Remaining Risk | Status |
|---|---|---|---|---|---|---|
| how-to-system-performance.md | YES | Evidence-based claims, no fake numbers, hot path awareness | 0 | N/A | None | GREEN |
| how-to-document.md | YES | Evidence location in EVIDENCE/v5.5/ | 0 | N/A | None | GREEN |
| how-to-coding-standards.md | YES | strict_types, clean code | 0 | N/A | None | GREEN |
| how-to-design-components.md | YES | Canonical component shape for benchmark infrastructure | 0 | N/A | None | GREEN |
| how-to-unit-test.md | YES | Tests for benchmark tooling | 0 | N/A | None | GREEN |
| how-to-system-security.md | YES | No secrets in benchmark output | 0 | N/A | None | GREEN |
| how-to-production-readiness.md | YES | Honest reporting, no marketing claims | 0 | N/A | None | GREEN |
| how-to-code-review.md | YES | Review of benchmark infrastructure | 0 | N/A | Pending | GREEN |
| how-to-architecture.md | YES | Folder says capability, unit says responsibility | 0 | N/A | None | GREEN |
| how-to-dogfooding.md | PARTIAL | Benchmark infrastructure uses AvaX components | 0 | N/A | Benchmark tooling is internal, not dogfooded yet | GREEN |
| how-to-modern-php-attributes-di.md | NO | Benchmark tooling does not use attributes | N/A | N/A | Not applicable to benchmark infrastructure | GREEN |
| how-to-clean-code.md | YES | Clean benchmark API | 0 | N/A | None | GREEN |
| how-to-code-style.md | YES | PHP 8.x style | 0 | N/A | None | GREEN |
| how-to-architecture-extension-with-ddd.md | NO | Benchmark tooling is not DDD | N/A | N/A | Not applicable | GREEN |
| how-to-use-advanced-architecture-patterns.md | NO | Benchmark tooling is straightforward | N/A | N/A | Not applicable | GREEN |

## Status

**V5.5-00: GREEN**
- Benchmark methodology locked
- Canonical result format defined
- Governance compliance matrix complete
- No violations found
- Methodology will be enforced in all subsequent V5.5 stages
