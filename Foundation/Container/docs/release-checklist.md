# Release Checklist

This checklist is the canonical release proof ledger for the component root.

## Required Green Gates

- lint: `find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l`
- smoke suite: [`../tests/run-smoke-tests.sh`](../tests/run-smoke-tests.sh)
- diagnostics contracts: [`../tests/check-diagnostics-contracts.sh`](../tests/check-diagnostics-contracts.sh)
- benchmark guard: [`../tests/check-benchmarks.sh`](../tests/check-benchmarks.sh)
- peer benchmark gate when peer artifacts are available: [
  `../tests/check-peer-benchmarks.sh`](../tests/check-peer-benchmarks.sh)

## Deterministic Ordering Proof

The release proof for deterministic ordering is green only when these pass:

- [
  `../tests/Flows/CreateContainer/DeterministicOrderingSmokeTest.php`](../tests/Flows/CreateContainer/DeterministicOrderingSmokeTest.php)
- [`../tests/Flows/BootProviders/BootProvidersSmokeTest.php`](../tests/Flows/BootProviders/BootProvidersSmokeTest.php)

They cover:

- provider order
- tag order
- decoration order
- compiled artifact ordering

## Compatibility Proof

The release proof for artifact compatibility is green only when these pass:

- [
  `../tests/Flows/CreateContainer/CompiledCompatibilitySmokeTest.php`](../tests/Flows/CreateContainer/CompiledCompatibilitySmokeTest.php)
- [
  `../tests/Flows/CreateContainer/CompiledSchemaCompatibilitySmokeTest.php`](../tests/Flows/CreateContainer/CompiledSchemaCompatibilitySmokeTest.php)
- [
  `../tests/Flows/CreateContainer/CompiledFreshnessSmokeTest.php`](../tests/Flows/CreateContainer/CompiledFreshnessSmokeTest.php)
- [
  `../tests/Flows/CreateContainer/CompiledIntegritySmokeTest.php`](../tests/Flows/CreateContainer/CompiledIntegritySmokeTest.php)

## Final Claim Gate

Do not claim "world-class" from this component root until:

- docs index links the canonical operator docs
- public contract matrix matches the actual surface
- diagnostics contracts are versioned and validated
- lifetime/scope, conditional composition, policy engine, and error-model docs are up to date
- benchmark thresholds are green
- peer comparison policy is documented
- world-class readiness dossier is up to date
