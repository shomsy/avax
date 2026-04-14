# Quality Gates Mapping — Auth Implementation

Version: 1.0.0
Status: Normative / Local
Scope: `Foundation/Auth/.agents/.rules/governance/core/quality/**`

This document maps the 13 universal quality gates to concrete Auth CI/automation.

## Gate → CI Mapping

| # | Quality Gate | CI Command | Automation Status |
|---|--------------|-----------|------------------|
| 1 | **Trust** (fail closed when mandatory config, dependency, policy input is missing) | `phpstan analyse` + `phpunit` | ✅ Automated |
| 2 | **Operator Clarity** (failure, recovery path, artifact location understandable) | `phpstan --error-format=table` | ✅ Partially Automated |
| 3 | **Rollback Posture** (explicit path to reverse/contain change) | `composer rollback:proof` | ✅ Automated |
| 4 | **Contract Stability** (public interfaces stable, migration path documented) | `phpstan analyse` + `rector --dry-run` | ✅ Automated |
| 5 | **State Ownership** (durable truth in owned boundary) | Integration tests + adapter tests | ✅ Automated via tests |
| 6 | **Async Containment** (acknowledgement, retry, timeout rules explicit) | Integration tests | ✅ Automated via tests |
| 7 | **Deterministic Automation** (CI can consume result without manual parsing) | All CI commands → JSON output | ✅ Automated |
| 8 | **Observability Logic** (logs, traces, metrics, events make behavior diagnosable) | Code review + log format checks | ⚠️ Manual |
| 9 | **Runtime Hardening** (least privilege, secret hygiene, runtime boundary) | Security tests + `secret-scan` | ✅ Automated |
| 10 | **Performance Posture** (latency, scale, throughput claims measured) | `infection` (mutation testing) | ✅ Automated |
| 11 | **Source Truth** (README, help text describe shipped system accurately) | Documentation sync review | ⚠️ Manual |
| 12 | **Evidence Integrity** (validation proof present, machine-readable, tied to change) | Release evidence scripts | ✅ Automated |
| 13 | **Self-Healing Loop** (findings fixed, revalidated, or turned into tracked backlog) | TODO tracking in management/ | ⚠️ Manual |

---

## CI Commands Reference

```bash
# Static Analysis
phpstan analyse                       # Basic PHPStan
phpstan analyse --memory-limit=1G    # With memory
phpstan analyse -c phpstan.strict.neon  # Strict rules

# Testing
phpunit                             # Unit tests
vendor/bin/infection                # Mutation testing

# Security
composer secret-scan               # Scan for committed secrets
composer dependency-review         # Dependency vulnerability check

# Release Gates
composer sbom                       # Software Bill of Materials
composer release:provenance         # Build provenance
composer rollback:proof            # Rollback evidence

# Code Quality
rector process --dry-request       # BC check
```

---

## Composr Scripts Defined

```json
{
  "test": "phpunit",
  "analyse": "phpstan analyse --memory-limit=1G",
  "analyse:strict": "phpstan analyse --memory-limit=1G -c phpstan.strict.neon",
  "dependency-review": "php tooling/review-composer-dependencies.php",
  "sbom": "php tooling/generate-sbom.php",
  "secret-scan": "php tooling/scan-committed-secrets.php",
  "release:provenance": "php tooling/create-release-provenance.php",
  "rollback:proof": "php tooling/create-rollback-evidence.php",
  "rector": "rector process --dry-run",
  "mutation": "tooling/run-with-coverage-driver vendor/bin/infection"
}
```

---

## CI Workflows

### quality.yml
```yaml
# Runs on: pull_request, push to main
jobs:
  validate:
    steps:
      - phpstan analyse
      - phpstan analyse:strict
      - phpunit test
      - composer dependency-review
      - composer secret-scan
```

### release-hardening.yml
```yaml
# Runs on: tag push (v*)
jobs:
  evidence:
    steps:
      - composer analyse
      - composer test
      - composer dependency-review > build/release/
      - composer secret-scan > build/release/
      - composer sbom > build/release/
      - composer release:provenance > build/release/
      - composer rollback:proof > build/release/
```

---

## Gaps / Manual Steps

| Gate | Gap | Recommended Action |
|---|---|---|
| 2 | Operator clarity - detailed error messages | Add `--error-format=table` parsing |
| 8 | Observability logic | Add observability test suite |
| 11 | Source truth sync | Add docs-lint CI step |
| 13 | Self-healing loop | Add TODO sync automation |

---

## Usage

To run all quality gates:

```bash
composer install
composer test           # Gates 1, 5, 6, 10
composer analyse        # Gates 1, 2, 4
composer analyse:strict # Gates 1, 2, 4 (strict)
composer secret-scan   # Gate 9
composer dependency-review  # Gate 9
composer rollback:proof  # Gate 3
```

To run release gates:

```bash
composer release:gate  # All 5 release scripts
```

---

*This mapping is authoritative for `avax/auth` package.*