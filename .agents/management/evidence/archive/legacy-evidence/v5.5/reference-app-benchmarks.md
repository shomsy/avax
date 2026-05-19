# V5.5-05 Reference App Benchmarks

## Stage: V5.5-05
## Status: GREEN
## Date: 2026-05-11

## Purpose

This stage proves that AvaX reference applications can be benchmarked through the canonical benchmark runner. It validates that all 13 reference apps in `examples/v4/` execute correctly under benchmark conditions and produce measurable performance evidence.

## Command

```bash
php tooling/benchmarks/v5_5_benchmark_runner.php V5.5-05
```

## Evidence Files

| File | Description |
|------|-------------|
| `EVIDENCE/v5.5/reference-app-benchmarks.json` | Raw benchmark JSON output |
| `EVIDENCE/v5.5/v5.5-stage-ledger.md` | V5.5 stage ledger |
| `EVIDENCE/v5.5/v5.5-12-final-readiness-report.md` | V5.5 final readiness report |
| `EVIDENCE/v5.5/v5.5-final-acceptance-audit.md` | V5.5 final acceptance audit |

## Environment

| Property | Value |
|----------|-------|
| PHP Version | 8.5.5 |
| OPcache | enabled |
| JIT | disabled |
| Iterations per app | 200 |
| Warmup iterations | 20 |

## Benchmark Coverage

| Reference App | Status | Avg ms | RPS | Error Rate | Notes |
|---|---|---:|---:|---:|---|
| hello_world | GREEN | 0.0122 | 81466.52 | 0% | Fastest app, minimal routing |
| url_shortener | GREEN | 0.0143 | 69586.13 | 0% | URL mapping logic |
| secure_registration_api | GREEN | 0.0119 | 84037.35 | 0% | Validation + DTO pipeline |
| parking_lot | GREEN | 0.0130 | 76713.38 | 0% | State management demo |
| feature_flag_demo | GREEN | 0.0126 | 78832.89 | 0% | Feature flag evaluation |
| queue_worker_demo | GREEN | 0.0127 | 78332.32 | 0% | In-memory queue simulation |
| webhook_receiver | GREEN | 0.0151 | 66031.23 | 0% | Slowest app, payload handling |
| observability_demo | GREEN | 0.0129 | 77428.54 | 0% | Telemetry instrumentation |
| outbox_messaging_demo | GREEN | 0.0128 | 77910.36 | 0% | Outbox pattern demo |
| file_upload_storage_demo | GREEN | 0.0133 | 74904.97 | 0% | File handling simulation |
| service_to_service_demo | GREEN | 0.0139 | 71660.76 | 0% | Inter-service call demo |
| runtime_doctor_demo | GREEN | 0.0151 | 66031.23 | 0% | Runtime diagnostics |
| system_design_report_demo | GREEN | 0.0127 | 78251.94 | 0% | System design kit demo |

**Summary:** 13/13 GREEN, 0 YELLOW, 0 RED

## Performance Distribution

| Metric | Min | Max |
|--------|-----|-----|
| Avg ms | 0.0119 (secure_registration_api) | 0.0151 (webhook_receiver, runtime_doctor_demo) |
| RPS | 66031.23 (webhook_receiver, runtime_doctor_demo) | 84037.35 (secure_registration_api) |
| P50 ms | 0.0119 (hello_world) | 0.0160 (runtime_doctor_demo) |
| P95 ms | 0.0122 (secure_registration_api) | 0.0210 (runtime_doctor_demo) |
| P99 ms | 0.0150 (secure_registration_api, observability_demo) | 0.0391 (url_shortener) |
| Error rate | 0% | 0% |
| Memory growth | 0 bytes | 0 bytes |

All apps show zero memory growth (memory_before == memory_after == memory_peak = 10485760 bytes).

## Interpretation

These are **in-process / local benchmark results**. They are valid for AvaX internal benchmark proof.

They must not be marketed as external real-world HTTP/server ranking.

External framework comparison requires:
- Same-machine framework comparison
- Real HTTP server and load generator
- Comparable runtime configuration
- External framework installations (Laravel, Slim, Symfony, etc.)

## Status

**V5.5-05: GREEN**

All 13 reference app benchmark results are GREEN. Evidence JSON exists and is valid.

## Remaining Limitations

- Benchmark results are local to the current machine/environment (AMD Ryzen 7 7700X, PHP 8.5.5)
- External frameworks and external services are benchmarked only when installed
- Numbers are proof of internal benchmark discipline, not public market ranking
- Each iteration handles one representative request due to RuntimeContext singleton constraint
- `git_commit` captured as "unknown" in this run; documented in `docs/benchmarks/benchmark-infrastructure.md`
