# Ownership-Aware Composition Program

This document is the canonical baseline and implementation program for the ownership-aware composition wave.

## Current Shape

The current system root is this component root.

Canonical production structure:

- `src/Container.php`
- `src/ContainerInterface.php`
- `src/ContextContainer.php`
- `src/Flows/`
- `src/Capabilities/`
- `src/Foundation/`

## Target Shape

The target container reads flow-first:

- `src/Container.php`
- `src/ContainerInterface.php`
- `src/ContextContainer.php`
- `src/Flows/`
- `src/Capabilities/Declaration/`
- `src/Capabilities/Composition/`
- `src/Capabilities/Resolution/`
- `src/Capabilities/Execution/`
- `src/Capabilities/Runtime/`
- `src/Capabilities/Diagnostics/`
- `src/Foundation/`

## Source Of Truth

- authored registration truth: `src/Capabilities/Declaration/Bindings/ServiceRegistry.php`
- authored ownership truth: `src/Capabilities/Declaration/Ownership/RegistrationMetadata.php`
- generated compile truth: `src/Capabilities/Composition/Compilation/CompileContainer.php` plus
  `src/Capabilities/Composition/Compilation/ArtifactMetadata.php`
- disposable runtime truth: `src/Capabilities/Resolution/ServiceResolver.php`,
  `src/Capabilities/Runtime/ServicePool.php`, `src/Capabilities/Runtime/Scopes/ScopeStore.php`

Compiled artifacts remain derived outputs only.

## Migration Posture

- new ownership work lands only in the target shape above
- no legacy shadow architecture is allowed
- no generic buckets are allowed
- `CreateContainer` remains the single flow owner for assembly
- public DX stays on `ContainerInterface` and `Container`
- ownership is enforced through authored metadata, validation, and diagnostics first

## Validation And Benchmark Map

Canonical local checks for this wave:

- lint:
  `docker run --rm -v "$PWD:/app" -w /app php:8.3-cli sh -lc "find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l"`
- smoke suite: `./tests/run-smoke-tests.sh`
- diagnostics contracts: `./tests/check-diagnostics-contracts.sh`
- benchmark guard: `./tests/check-benchmarks.sh`
- peer benchmark gate: `./tests/check-peer-benchmarks.sh peer=/absolute/path/to/peer-report.json`

## Implementation Order

1. baseline and backlog discipline
2. authored ownership metadata
3. slice visibility and import/export validation
4. explainability and graph diagnostics
5. docs, evidence, and release-proof verification

## Acceptance Anchor

This wave is only considered complete when:

- authored ownership metadata is queryable
- slice access rules are validated and explained
- runtime stays honest about private/internal/exported boundaries
- compiled metadata carries derived ownership shape without becoming source of truth
- docs and evidence describe the same model
