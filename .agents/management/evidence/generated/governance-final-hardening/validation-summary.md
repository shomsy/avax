# Validation Summary

**Date:** 2026-05-26
**Branch:** `architecture/identity-runtime-convergence`
**Status:** RED_BLOCKED

## Commands Run

| Command | Exit | Result |
|---|---:|---|
| `git diff --check` | 0 | PASS |
| `composer dump-autoload -o` | 0 | PASS with PSR-4 warnings |
| `vendor/bin/phpunit` | 0 | PASS: `OK (9551 tests, 27380 assertions)` |
| `vendor/bin/phpstan analyse` | 255 | FAIL: PHP memory limit 128M exhausted before analysis completed |
| `vendor/bin/phpstan analyse --memory-limit=1G` | 1 | FAIL: analysis completed and found 100 errors |
| `php tooling/governance/check-governance-canonical-truth.php` | 0 | GREEN |
| `php tooling/governance/check-governance-leakage.php` | 0 | GREEN |
| `php tooling/governance/check-governance-index-current.php` | 0 | GREEN |
| `php tooling/governance/check-stage-lock.php` | 0 | PASS |
| `php tooling/governance/check-self-explaining-architecture.php` | 1 | FAIL |
| `php tooling/testing/check-shallow-tests.php` | 1 | FAIL |
| `php -l tooling/governance/check-governance-canonical-truth.php` | 0 | PASS |
| `php -l tooling/governance/check-governance-leakage.php` | 0 | PASS |
| `php -l tooling/governance/generate-review-packs.php` | 0 | PASS |

## Exact Outputs

### `composer dump-autoload -o`

```text
Generating optimized autoload files
Class Avax\Tests\Unit\Components\Identity\Auth\Login\RateLimitExceptionTest located in ./tests/Unit/Components/Identity/Auth/Login/RateLimit/RateLimitExceptionTest.php does not comply with psr-4 autoloading standard (rule: Avax\Tests\ => ./tests). Skipping.
Class xhp_ located in ./framework/System/Foundation/compat.php does not comply with psr-4 autoloading standard (rule: Avax\Framework\ => ./framework). Skipping.
Generated optimized autoload files containing 9497 classes
```

### `vendor/bin/phpunit`

```text
PHPUnit 10.5.63 by Sebastian Bergmann and contributors.
Runtime: PHP 8.5.5
Configuration: /home/shomsy/projects/avax-auth-rewrite-v2/phpunit.xml
Time: 00:34.679, Memory: 98.50 MB
OK (9551 tests, 27380 assertions)
```

### `vendor/bin/phpstan analyse`

```text
Fatal error: Allowed memory size of 134217728 bytes exhausted
PHPStan process crashed because it reached configured PHP memory limit: 128M
Increase your memory limit in php.ini or run PHPStan with --memory-limit CLI option.
```

### `vendor/bin/phpstan analyse --memory-limit=1G`

```text
[ERROR] Found 100 errors
```

Representative findings:

```text
components/Identity/Access/System/Capabilities/Policy/Engine/PolicyEvaluator.php:41
Parameter #2 $reasons of DecisionExplanation constructor expects list<string>, list<string|null> given.

components/Identity/Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php:214
Call to new Diagnostics() on a separate line has no effect.

tests/Unit/Components/Identity/Access/AccessCharacterizationTest.php:21
Attribute class Avax\Tests\Unit\Components\Identity\Access\Override does not exist.
```

### Governance Canonical Truth

```text
GREEN: Governance canonical truth is coherent.
No root shadow how-to files remain.
Every canonical governance file is listed exactly once.
Planned docs are not treated as required governance.
Evidence is not mandatory canonical governance.
```

### Governance Leakage

```text
Files checked: 26
Files with leaks: 0
Total findings: 0
GREEN: No unapproved project leakage found in generic governance.
```

### Governance Index

```text
GREEN: Governance index is current.
```

### Stage Lock

```text
V1 Kernel Green: PROVEN
V2 Implementation: UNKNOWN
V3 Implementation: CLOSED / GREEN (SystemDesignKit promoted to components/SystemDesign)
Active Stage: **REMEDIATION_ACTIVE — All P0 BLOCKERs CLOSED (0/7 remaining)**
Allowed: next stage work according to EVIDENCE/EXECUTION.md; V2 only when explicitly unlocked there
FORBIDDEN: V3/V4 production implementation until their locks are lifted
```

### Self-Explaining Architecture

```text
BLOCKER: 0
HIGH: 172
MEDIUM: 184
LOW: 207
Total findings: 563
```

### Shallow Test Detection

```text
Files scanned: 432
Shallow files: 222
Total findings: 392
HIGH: 345 (security-sensitive shallow tests)
MEDIUM: 47 (general shallow tests)
LOW: 0
```

## Validation Decision

FULL_GREEN_ENTERPRISE_READY is forbidden.

Reason:

```text
- PHPStan has 100 active errors after rerun with sufficient memory.
- Self-explaining architecture gate has 172 HIGH findings.
- Shallow-test gate has 345 HIGH findings and 47 MEDIUM findings.
```

Review packs were not regenerated because the prompt requires fresh packs only after validation passes.
