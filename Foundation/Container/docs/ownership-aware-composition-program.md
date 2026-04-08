# Ownership-Aware Composition Program

This document is the canonical baseline and implementation program for the ownership-aware composition wave.

## Current Shape

The current system root is this component root.

Canonical production structure:

- `Container.php`
- `ContainerInterface.php`
- `DependencyInjection/Flows/`
- `DependencyInjection/Dependencies/`
- `DependencyInjection/Injection/`
- `DependencyInjection/Scopes/`
- `Configuration/`
- `Compilation/`
- `Runtime/`
- `Observability/`
- `Errors/`
- `Foundation/`

## Target Shape

The target container keeps the same canonical tree, but adds one narrow authored ownership lane:

- `DependencyInjection/Dependencies/Ownership/`

That lane owns:

- registration ownership metadata
- category and visibility vocabularies
- import/export posture
- slice manifests for diagnostics

It does not own runtime resolution, scope storage, or artifact lifecycle.

## Source Of Truth

- authored registration truth: `DependencyInjection/Dependencies/Bindings/ServiceRegistry.php`
- authored ownership truth: `DependencyInjection/Dependencies/Ownership/RegistrationMetadata.php`
- generated compile truth: `Compilation/CompileContainer.php` plus `Compilation/ArtifactMetadata.php`
- disposable runtime truth: `DependencyInjection/Dependencies/Resolution/ServiceResolver.php`, `Runtime/ServicePool.php`, `DependencyInjection/Scopes/ScopeStore.php`

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

- lint: `docker run --rm -v "$PWD:/app" -w /app php:8.3-cli sh -lc "find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l"`
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
