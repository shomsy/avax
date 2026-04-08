# Container Docs

This component has one canonical story:

- public facade: [`Container.md`](./Container.md)
- architecture map: [`architecture.md`](./architecture.md)
- concepts: [`concepts/index.md`](./concepts/index.md)
- glossary: [`glossary.md`](./glossary.md)
- failure handling: [`troubleshooting.md`](./troubleshooting.md)
- benchmark harness: [`../tests/run-benchmarks.sh`](../tests/run-benchmarks.sh)
- benchmark guard: [`../tests/check-benchmarks.sh`](../tests/check-benchmarks.sh)
- benchmark comparison: [`../tests/run-benchmark-comparison.sh`](../tests/run-benchmark-comparison.sh)

Canonical source tree:

- `Container.php`
- `ContainerInterface.php`
- `DependencyInjection/Flows/`
- `DependencyInjection/Dependencies/`
- `DependencyInjection/Injection/`
- `DependencyInjection/Scopes/`
- `Configuration/`
- `Observability/`
- `Errors/`
- `Foundation/`
- `docs/`

Local validation:

- lint: `docker run --rm -v "$PWD:/app" -w /app php:8.3-cli sh -lc "find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l"`
- smoke suite: [`tests/run-smoke-tests.sh`](../tests/run-smoke-tests.sh)
- benchmarks: [`tests/run-benchmarks.sh`](../tests/run-benchmarks.sh)
- benchmark guard: [`tests/check-benchmarks.sh`](../tests/check-benchmarks.sh)
- benchmark comparison: [`tests/run-benchmark-comparison.sh`](../tests/run-benchmark-comparison.sh)

Performance model:

- reflect once
- compile once
- run many times from memory or versioned disk artifacts
- choose `minimal` diagnostics for low-overhead production runtime state
- choose `detailed` diagnostics for CI, debugging, and richer timeline output
- use the benchmark guard for threshold enforcement; it evaluates median timing across repeated runs to reduce CI noise
