# Quality Ratchet Baseline

## Status

This file is the canonical quality ratchet baseline for AvaX.

## Rules

- When a metric improves, the new value becomes the floor.
- Regression below the floor requires explicit YELLOW/RED with owner/expiry.
- If a metric is established at 0, it MUST stay at 0.

## Baseline

| Metric                               | Previous baseline   | Current value               | Command                                                                   | Owner                 | Blocks stage?                    |
|--------------------------------------|---------------------|-----------------------------|---------------------------------------------------------------------------|-----------------------|----------------------------------|
| PHPStan error count                  | 0                   | 0                           | `vendor/bin/phpstan analyse framework components tests --memory-limit=1G` | —                     | No                               |
| PHPUnit errors/failures              | 0                   | 0                           | `vendor/bin/phpunit --no-coverage`                                        | —                     | No                               |
| Runtime composition findings         | 140+ (pre-existing) | 140+                        | `php tooling/refactor/check-runtime-composition-leaks.php`                | —                     | Yes, for V5.9 GREEN              |
| Public surface violations            | 0                   | 0                           | `php tooling/refactor/check-public-surface.php`                           | —                     | No                               |
| Semantic PHPDoc violations           | N/A (new gate)      | 9823 legacy / 0 touched-new | `php tooling/governance/check-semantic-phpdoc.php`                        | AvaX governance owner | Yes, for touched/new regressions |
| Security commit block readiness      | N/A (new gate)      | PASS                        | `php tooling/governance/check-security-commit-block-readiness.php`        | —                     | No                               |
| How-to document structure violations | N/A (new gate)      | TBD                         | `php tooling/governance/check-how-to-document-structure.php`              | —                     | No                               |
