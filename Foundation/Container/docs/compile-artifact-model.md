# Compile Artifact Model

Compiled artifacts are generated outputs. They are never the source of truth.

## Source Of Truth

- authored truth: `ServiceRegistry`
- generated compile outputs: compiled PHP artifact, sidecar JSON metadata, blueprint cache files
- disposable runtime state: attached compiled runtime, `ServicePool`, `ScopeStore`, lazy markers, metrics, timeline

## Artifact Set

For one cache version the compiler owns:

- `compiled/container.php`
- `compiled/container.json`
- `compiled/quarantine/`

The artifact pair can be deleted and regenerated without re-registering services.

## Metadata Contract

`ArtifactMetadata` is the typed sidecar contract. It records:

- `format`
- `schemaVersion`
- `cacheVersion`
- `configHash`
- `settingsFingerprint`
- `environment`
- `compileMode`
- `diagnosticsMode`
- `strict`
- `fingerprint`
- `dependencyGraphRevision`
- `artifactPaths`
- `warmed`
- `benchmarkBuildMarker`
- ordered service entries and per-service signatures
- aliases, tags, lifetimes, deferred flags, decoration counts
- changed services, invalidated services, invalidation reasons
- statistics and checksum

## Determinism Rules

The compiler keeps stable ordering for:

- service ids
- aliases
- tags
- lifetime maps
- deferred maps
- decoration counts
- per-service dependency lists

`compiledAt` and artifact paths are intentionally environment-specific. They are not part of the semantic ordering proof.

## Compatibility Rules

The runtime rejects the compiled path when any of these drift:

- `schemaVersion`
- `cacheVersion`
- `configHash`
- `environment`
- `compileMode`
- `diagnosticsMode`
- `strict`
- service signatures for the requested entries
- checksum

See [`compile-artifact-compatibility.md`](./compile-artifact-compatibility.md) for the full behavior matrix.

## Lifecycle

- `compileContainer()` writes a generated artifact set
- `warmCompiled()` writes the same set and marks it warmed
- `flushCompiled()` removes only compile outputs
- `rebuildCompiled()` flushes then recompiles
- `flush()` clears compile outputs plus disposable runtime state
- `reset()` keeps compile outputs and authored registrations, but clears disposable runtime state
