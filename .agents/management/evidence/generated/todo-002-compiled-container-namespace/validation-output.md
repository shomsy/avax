# Validation Output — TODO-002 Compiled Container Namespace Emission Fix

## Commands Run

### 1. composer validate --no-check-publish
```
./composer.json is valid
```
Status: PASS

### 2. composer dump-autoload -o
```
Generating optimized autoload files
Class xhp_ located in ./framework/System/Foundation/compat.php does not comply with psr-4 autoloading standard (rule: Avax\Framework\ => ./framework). Skipping.
Generated optimized autoload files containing 9351 classes
```
Status: PASS (pre-existing xhp_ warning, unrelated to this change)

### 3. vendor/bin/phpunit --filter "CompiledContainerNamespaceEmission|MethodEmitter|CompileContainer" --no-coverage
```
OK (16 tests, 38 assertions)
```
Status: PASS

### 4. vendor/bin/phpstan analyse components/Application/Container/System/Capabilities/Composition/Compilation tests/Unit/Components/Application/Container/CompiledContainerNamespaceEmissionTest.php --memory-limit=1G --error-format=raw --no-progress
```
(no output — clean)
```
Status: PASS

### 5. php tooling/refactor/check-namespace-drift.php
```
PASS
```
Status: PASS

### 6. php tooling/refactor/check-broken-reference-semantics.php
```
SKIPPED (OPTIONAL_PHP_EXTENSION): 2 refs (Memcached, Redis)
SKIPPED (TEST_FIXTURE): 1 refs (NonExistentResourceType)
SKIPPED (OPTIONAL_VENDOR): 3 refs (RoadRunner, RuleDocGenerator)
FAIL: 5 active broken references (HTTP Middleware, ApplicationWorkflow — pre-existing, unrelated to Container)
```
Status: FAIL_EXPECTED_FINDINGS — 5 pre-existing broken references in HTTP/Operations, none in Container scope.

### 7. php tooling/refactor/check-runtime-composition-leaks.php
```
PASS
```
Status: PASS

### 8. php tooling/governance/check-governance-index-current.php
```
GREEN: Governance index is current.
```
Status: PASS

### 9. php tooling/governance/check-root-evidence-hygiene.php
```
GREEN: Root evidence hygiene PASSED.
```
Status: PASS

## Summary

| Command | Status | Notes |
|---|---|---|
| composer validate | PASS | |
| composer dump-autoload | PASS | pre-existing xhp_ warning |
| phpunit | PASS | 16 tests, 38 assertions |
| phpstan | PASS | clean |
| check-namespace-drift | PASS | |
| check-broken-reference-semantics | FAIL_EXPECTED | 5 pre-existing, unrelated |
| check-runtime-composition-leaks | PASS | |
| check-governance-index-current | PASS | |
| check-root-evidence-hygiene | PASS | |

All validation gates pass for the Container scope. The 5 broken references in check-broken-reference-semantics.php are pre-existing in HTTP/Operations components and are outside the TODO-002 scope.
