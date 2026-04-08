# Benchmark Governance

This component treats benchmark claims as a governed release input, not as ad hoc local scripts.

## Canonical Entrypoints

- harness: [`tests/run-benchmarks.sh`](../tests/run-benchmarks.sh)
- regression guard: [`tests/check-benchmarks.sh`](../tests/check-benchmarks.sh)
- comparison runner: [`tests/run-benchmark-comparison.sh`](../tests/run-benchmark-comparison.sh)
- thresholds: [`tests/benchmarks/thresholds.php`](../tests/benchmarks/thresholds.php)

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
- relative comparison ratios when two JSON artifacts are compared

The regression guard uses repeated runs and median timing to reduce single-run noise.

## Parity Rules

When comparing against a peer container:

- use the same PHP major/minor version
- use the same machine class or CI lane when possible
- keep graph shapes and iteration counts identical
- keep warm/cold/scoped/deferred semantics aligned instead of comparing unrelated behaviors
- compare JSON benchmark artifacts, not hand-copied numbers

## Release Policy

- `tests/check-benchmarks.sh` is the release gate for this component root
- threshold changes must be intentional and reviewed together with the code change that requires them
- performance regressions above threshold are blocking unless explicitly accepted as a tradeoff
- comparison artifacts support release notes and peer claims, but they do not replace the local regression guard

## Operator Guidance

- use the guard for "did we regress?"
- use the harness for "what changed?"
- use the comparison runner for "how does this build compare against another benchmark artifact?"
- use `compileReport()` and `runtimeReport()` alongside benchmarks when a compiled/runtime mode decision changes the result
