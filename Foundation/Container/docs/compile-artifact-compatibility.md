# Compile Artifact Compatibility

This page defines what the runtime does when compiled artifacts do not match the current build.

## Compatibility Inputs

The compiled path is rejected when these no longer match:

- schema version
- cache version
- config hash
- environment
- compile mode
- diagnostics mode
- strictness
- per-service signatures for requested entries
- checksum

## Freshness States

- `missing`: metadata or artifact is absent
- `incompatible`: compatibility inputs differ
- `partial`: requested service ids are not all present in the artifact
- `corrupt`: checksum or compiled source load failed
- `stale`: service signatures drifted
- `fresh`: artifact is compatible, complete, and current

## Runtime Behavior Matrix

| Condition | Dev | CI | Warmup | Production |
|:--|:--|:--|:--|:--|
| missing artifact | dynamic fallback | dynamic fallback with CI diagnostics | dynamic fallback before warm compile | dynamic fallback |
| incompatible artifact | dynamic fallback | dynamic fallback with CI diagnostics | dynamic fallback before rebuild | dynamic fallback |
| stale artifact | dynamic fallback | dynamic fallback with CI diagnostics | dynamic fallback before rebuild | dynamic fallback |
| corrupt artifact | quarantine + dynamic fallback | quarantine + fail closed | quarantine + fail closed | quarantine + fail closed |

Corruption is fail-closed in production-style modes. Compatibility and freshness drift reject the compiled path deterministically and leave the runtime on the dynamic path until the artifact is rebuilt.

## Canonical Commands

- inspect: `compileReport()`
- inspect runtime attachment: `runtimeReport()`
- clear compile outputs: `flushCompiled()`
- rebuild: `rebuildCompiled()`
- full clear: `flush()`

## Regression Proof

Compatibility and freshness proofs live in:

- [`../tests/DependencyInjection/Flows/CreateContainer/CompiledCompatibilitySmokeTest.php`](../tests/DependencyInjection/Flows/CreateContainer/CompiledCompatibilitySmokeTest.php)
- [`../tests/DependencyInjection/Flows/CreateContainer/CompiledSchemaCompatibilitySmokeTest.php`](../tests/DependencyInjection/Flows/CreateContainer/CompiledSchemaCompatibilitySmokeTest.php)
- [`../tests/DependencyInjection/Flows/CreateContainer/CompiledFreshnessSmokeTest.php`](../tests/DependencyInjection/Flows/CreateContainer/CompiledFreshnessSmokeTest.php)
- [`../tests/DependencyInjection/Flows/CreateContainer/CompiledIntegritySmokeTest.php`](../tests/DependencyInjection/Flows/CreateContainer/CompiledIntegritySmokeTest.php)
