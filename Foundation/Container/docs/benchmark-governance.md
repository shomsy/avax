# Benchmark Governance

This component treats benchmark claims as a governed release input, not as ad hoc local scripts.

## Canonical Entrypoints

- harness: [`tests/run-benchmarks.sh`](../tests/run-benchmarks.sh)
- regression guard: [`tests/check-benchmarks.sh`](../tests/check-benchmarks.sh)
- comparison runner: [`tests/run-benchmark-comparison.sh`](../tests/run-benchmark-comparison.sh)
- peer comparison runner: [`tests/run-peer-benchmark-matrix.sh`](../tests/run-peer-benchmark-matrix.sh)
- peer regression gate: [`tests/check-peer-benchmarks.sh`](../tests/check-peer-benchmarks.sh)
- thresholds: [`tests/benchmarks/thresholds.php`](../tests/benchmarks/thresholds.php)
- peer thresholds: [`tests/benchmarks/peer-thresholds.php`](../tests/benchmarks/peer-thresholds.php)

## Canonical Suite

The shipped suite currently gates these scenarios:

- `cold_boot`
- `warm_boot`
- `cached_get`
- `worker_cached_get`
- `uncached_resolve`
- `request_lifecycle`
- `deep_graph`
- `wide_graph`
- `scoped_service`
- `lazy_service`
- `deferred_service`
- `deferred_provider`
- `function_call_injection`
- `property_injection`
- `method_injection`
- `compile_time`
- `prod_compiled_get`
- `dev_compiled_get`

These names are canonical. Thresholds and comparison output should use the same names.

## Metrics

The benchmark harness records:

- elapsed time in milliseconds
- peak memory in megabytes
- memory per operation in kilobytes
- relative comparison ratios when two JSON artifacts are compared

The regression guard uses repeated runs and median timing to reduce single-run noise.
The peer comparison lane also expects guard-mode artifacts so competitor comparisons are not driven by single noisy
samples.

The external competitor lane is driven through an explicit adapter contract under:

- [`../tests/benchmarks/adapters/BenchmarkPeerAdapter.php`](../tests/benchmarks/adapters/BenchmarkPeerAdapter.php)
- [
  `../tests/benchmarks/adapters/ArtifactBenchmarkPeerAdapter.php`](../tests/benchmarks/adapters/ArtifactBenchmarkPeerAdapter.php)

Benchmark JSON artifacts also carry:

- PHP version
- SAPI
- fixed Docker image name
- canonical PHP settings snapshot
- suite version
- optional build marker
- guard mode flag
- scenario names and scenario count

Peer matrix artifacts also carry:

- schema version
- subject and peer names
- per-peer parity issues
- per-scenario ratios versus each peer
- explicit regression rows

## Parity Rules

When comparing against a peer container:

- use the same PHP major/minor version
- use the same machine class or CI lane when possible
- keep graph shapes and iteration counts identical
- keep warm/cold/scoped/deferred semantics aligned instead of comparing unrelated behaviors
- compare JSON benchmark artifacts, not hand-copied numbers
- reject peer artifacts that do not match `dockerImage`, `php`, `sapi`, `phpSettings`, `suiteVersion`, and the canonical
  scenario set
- reject peer artifacts that are not produced in the same guard-mode policy

## Release Policy

- `tests/check-benchmarks.sh` is the release gate for this component root
- `tests/check-peer-benchmarks.sh` is the peer comparison release gate when competitor artifacts are available
- the canonical Docker image for local and CI benchmark runs is `php:8.3-cli`
- threshold changes must be intentional and reviewed together with the code change that requires them
- performance regressions above threshold are blocking unless explicitly accepted as a tradeoff
- set `BENCHMARK_ARTIFACT_DIR` in CI when benchmark artifacts must be retained after the job finishes
- peer comparison artifacts support release notes and external claims, but they do not replace the local regression
  guard

## Operator Guidance

- use the guard for "did we regress?"
- use the harness for "what changed?"
- use the comparison runner for "how does this build compare against another benchmark artifact?"
- use the peer comparison runner for "how does this build compare against a governed peer baseline under the same
  artifact contract?"
- use `compileReport()` and `runtimeReport()` alongside benchmarks when a compiled/runtime mode decision changes the
  result
