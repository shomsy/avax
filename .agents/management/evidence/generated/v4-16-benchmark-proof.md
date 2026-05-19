# V4-16 Benchmark Proof Report

- **Date**: 2026-05-10T15:48:40+00:00
- **PHP Version**: 8.5.5
- **Platform**: Linux

## Results

| Benchmark | Iterations | Total (s) | Avg (ms) | Min (ms) | Max (ms) | P95 (ms) |
|-----------|-----------|-----------|----------|----------|----------|----------|
| App Creation & Route Registration | 200 | 0.0005 | 0.0024 | 0.0019 | 0.0050 | 0.0031 |
| Route Matching (handle request) | 500 | 0.0040 | 0.0080 | 0.0069 | 0.0200 | 0.0081 |
| Policy Evaluation | 500 | 0.0004 | 0.0009 | 0.0000 | 0.0038 | 0.0012 |
| Request Signing & Verification | 200 | 0.0007 | 0.0035 | 0.0029 | 0.0081 | 0.0041 |
| Feature Flag Evaluation | 500 | 0.0002 | 0.0004 | 0.0000 | 0.0012 | 0.0012 |
| Health Liveness Check | 500 | 0.0001 | 0.0002 | 0.0000 | 0.0012 | 0.0010 |
| State Reset | 200 | 0.0001 | 0.0003 | 0.0000 | 0.0031 | 0.0010 |

## Verdict

All V4 benchmark capabilities executed successfully.
Route matching, policy evaluation, request signing, feature flags, health checks, and state reset all proved functional under load.
