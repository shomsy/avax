# Container Docs

This component has one canonical story:

- public facade: [`Container.md`](./Container.md)
- public contract matrix: [`public-contract-matrix.md`](./public-contract-matrix.md)
- build mode: [`build-mode.md`](./build-mode.md)
- runtime mode: [`runtime-mode.md`](./runtime-mode.md)
- compile artifact model: [`compile-artifact-model.md`](./compile-artifact-model.md)
- runtime state model: [`runtime-state-model.md`](./runtime-state-model.md)
- lifetimes and scopes: [`lifetimes-and-scopes.md`](./lifetimes-and-scopes.md)
- conditional composition: [`conditional-composition.md`](./conditional-composition.md)
- ownership-aware composition program: [
  `ownership-aware-composition-program.md`](./ownership-aware-composition-program.md)
- ownership model: [`ownership-model.md`](./ownership-model.md)
- testing composition: [`testing-composition.md`](./testing-composition.md)
- policy engine: [`policy-engine.md`](./policy-engine.md)
- error model: [`error-model.md`](./error-model.md)
- diagnostics contracts: [`diagnostics-contracts.md`](./diagnostics-contracts.md)
- artifact compatibility: [`compile-artifact-compatibility.md`](./compile-artifact-compatibility.md)
- worker/request lifecycle: [`worker-request-lifecycle.md`](./worker-request-lifecycle.md)
- CreateContainer audit: [`create-container-audit.md`](./create-container-audit.md)
- release checklist: [`release-checklist.md`](./release-checklist.md)
- world-class readiness: [`world-class-readiness.md`](./world-class-readiness.md)
- architecture map: [`architecture.md`](./architecture.md)
- concepts: [`concepts/index.md`](./concepts/index.md)
- glossary: [`glossary.md`](./glossary.md)
- ADRs: [`adr/index.md`](./adr/index.md)
- failure handling: [`troubleshooting.md`](./troubleshooting.md)
- benchmark governance: [`benchmark-governance.md`](./benchmark-governance.md)
- benchmark harness: [`../tests/run-benchmarks.sh`](../tests/run-benchmarks.sh)
- benchmark guard: [`../tests/check-benchmarks.sh`](../tests/check-benchmarks.sh)
- benchmark comparison: [`../tests/run-benchmark-comparison.sh`](../tests/run-benchmark-comparison.sh)
- peer benchmark matrix: [`../tests/run-peer-benchmark-matrix.sh`](../tests/run-peer-benchmark-matrix.sh)
- peer benchmark gate: [`../tests/check-peer-benchmarks.sh`](../tests/check-peer-benchmarks.sh)
- convergence map: [`architecture-convergence.md`](./architecture-convergence.md)

Canonical source tree:

- `src/Container.php`
- `src/ContainerInterface.php`
- `src/ContextContainer.php`
- `src/Flows/`
- `src/Capabilities/`
- `src/Foundation/`
- `docs/`

Local validation:

- lint:
  `docker run --rm -v "$PWD:/app" -w /app php:8.3-cli sh -lc "find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l"`
- smoke suite: [`tests/run-smoke-tests.sh`](../tests/run-smoke-tests.sh)
- benchmarks: [`tests/run-benchmarks.sh`](../tests/run-benchmarks.sh)
- benchmark guard: [`tests/check-benchmarks.sh`](../tests/check-benchmarks.sh)
- benchmark comparison: [`tests/run-benchmark-comparison.sh`](../tests/run-benchmark-comparison.sh)
- diagnostics contracts: [`tests/check-diagnostics-contracts.sh`](../tests/check-diagnostics-contracts.sh)
- peer benchmark gate: [`tests/check-peer-benchmarks.sh`](../tests/check-peer-benchmarks.sh)

Performance model:

- reflect once
- compile once
- run many times from memory or versioned disk artifacts
- keep canonical registrations separate from disposable runtime state
- keep lifetime, condition, and override truth authored in registrations and only projected into derived diagnostics
- choose `minimal` diagnostics for low-overhead production runtime state
- choose `detailed` or `ci` diagnostics for debugging, CI, and richer timeline output
- use the benchmark guard for threshold enforcement; it evaluates median timing across repeated runs to reduce CI noise
- keep ownership truth authored in registrations and derived in compiled diagnostics only
