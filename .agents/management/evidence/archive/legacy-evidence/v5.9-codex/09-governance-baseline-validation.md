# V5.9 Governance Baseline Validation

Stage: V5.9 Governance Baseline Classification, Gate Ratchet Correction & Next Blocker Selection

Status: YELLOW_WITH_EXACT_AUTHBUILDER_BLOCKER

Date: 2026-05-16

Raw evidence:

- `EVIDENCE/v5.9-codex/raw/governance-baseline-composer-validate.txt`
- `EVIDENCE/v5.9-codex/raw/governance-baseline-autoload.txt`
- `EVIDENCE/v5.9-codex/raw/governance-baseline-phpunit.txt`
- `EVIDENCE/v5.9-codex/raw/governance-baseline-phpstan.txt`
- `EVIDENCE/v5.9-codex/raw/governance-baseline-runtime-composition.txt`
- `EVIDENCE/v5.9-codex/raw/governance-baseline-runtime-assembly.txt`
- `EVIDENCE/v5.9-codex/raw/governance-baseline-public-surface.txt`
- `EVIDENCE/v5.9-codex/raw/governance-baseline-hollow-public-surface.txt`
- `EVIDENCE/v5.9-codex/raw/governance-baseline-governance-gates.txt`

| Command | Result | Count | Blocks commit? | Notes |
|---|---|---:|---:|---|
| `composer validate --no-check-publish` | PASS | 0 | NO | `composer.json` is valid. |
| `composer dump-autoload -o` | PASS_WITH_WARNING | 1 | NO | Existing PSR-4 warning for `framework/System/Foundation/compat.php` class `xhp_`; autoload generated 9341 classes. |
| `vendor/bin/phpunit --no-coverage` | PASS_WITH_DEPRECATION | 1 | NO | 8463 tests, 24341 assertions, 1 deprecation, 0 errors/failures. |
| `vendor/bin/phpstan analyse framework components tests --memory-limit=1G` | PASS | 0 | NO | 3900 files analysed, no errors. |
| `php tooling/refactor/check-runtime-composition-leaks.php` | PASS | 0 | NO | Runtime composition gate returned `PASS`. |
| `php tooling/components/check-component-runtime-assembly.php` | PASS | 0 | NO | 3129 active flow/surface files scanned, no hidden runtime assembly. |
| `php tooling/refactor/check-public-surface.php` | PASS | 0 | NO | PublicSurface gate returned `PASS`. |
| `php tooling/components/check-hollow-public-surfaces.php` | PASS | 0 | NO | 227 public surface files checked, no hollow active surfaces. |
| `php tooling/governance/check-truth-consistency.php` | PASS | 0 | NO | Truth consistency verified. |
| `php tooling/governance/check-semantic-phpdoc.php` | PASS_WITH_YELLOW_RATCHET | 9823 | NO | 9823 legacy untouched violations remain visible; 0 touched/new blocking violations; baseline floor is 9823. |
| `php tooling/governance/check-how-to-document-structure.php` | PASS | 0 | NO | 19 how-to files scanned, 0 structure violations. |
| `php tooling/governance/check-serviceprovider-governance-consistency.php` | PASS | 0 | NO | 19 files scanned, 0 ServiceProvider wording violations. |
| `php tooling/governance/check-canonical-terms.php` | PASS | 0 | NO | Canonical terms registry exists and includes required columns and critical terms. |
| `php tooling/governance/check-large-unit-thresholds.php` | EXPECTED_BLOCKER | 109 | YES_FOR_FULL_GREEN | 1 BLOCKER: `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`; 108 REVIEW findings tracked as review debt. |
| `php tooling/governance/check-quality-ratchet.php` | PASS_WITH_MANUAL_REVIEW | 7 | NO | Baseline exists; semantic PHPDoc recorded as `9823 legacy / 0 touched-new`. |
| `php tooling/governance/check-security-commit-block-readiness.php` | PASS | 0 | NO | All security commit block rules present. |
| `php tooling/governance/check-gate-self-tests.php` | PASS | 0 | NO | 10/10 gates present; 10/10 have self-test markers; 0 gates scan zero files. |

## Decision

The governance baseline is no longer a fake repo-wide RED caused by legacy PHPDoc debt. The Semantic PHPDoc gate now reports legacy debt as visible YELLOW ratchet debt and still blocks touched/new production violations. The how-to document structure gate is clean. The large-unit gate remains intentionally non-green because `AuthBuilder.php` is a real classified blocker and the next implementation phase.

This phase is commit-eligible only as `YELLOW_WITH_EXACT_AUTHBUILDER_BLOCKER`, not as full green.
