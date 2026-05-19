# V5.9 Codex Baseline Validation

Date: 2026-05-16
Stage: V5.9 Codex Deep Execution Program
Phase: Baseline validation before implementation
Status: RED_VALIDATION_OR_TRUTH_BROKEN

## 1. Validation Table

| Command | Result | Count | Blocks execution? | Notes |
|---|---|---:|---:|---|
| `composer validate --no-check-publish` | GREEN | 1 | NO | `composer.json is valid`. Raw: `raw/00-composer-validate-before.txt`. |
| `composer dump-autoload -o` | GREEN_WITH_WARNING | 9341 classes | NO | Optimized autoload generated. Pre-existing PSR-4 skip warning: `framework/System/Foundation/compat.php` contains `xhp_`. Raw: `raw/01-autoload-before.txt`. |
| `vendor/bin/phpunit --no-coverage` | GREEN_WITH_DEPRECATION | 8463 tests, 24341 assertions | NO | 0 errors, 0 failures, 1 deprecation. Raw output also includes test-intentional failure boundary log lines. |
| `vendor/bin/phpstan analyse framework components tests --memory-limit=1G` | GREEN | 3900 files | NO | 0 errors. |
| `php tooling/refactor/check-runtime-composition-leaks.php` | PASS | not reported | NO | Runtime composition gate passed. |
| `php tooling/components/check-component-runtime-assembly.php` | PASS | 3129 files | NO | No hidden runtime assembly. |
| `php tooling/refactor/check-public-surface.php` | PASS | not reported | NO | PublicSurface gate passed. |
| `php tooling/components/check-hollow-public-surfaces.php` | PASS | 227 files | NO | No hollow active PublicSurface files. |
| `php tooling/governance/check-truth-consistency.php` | PASS | 4 checks | NO | Truth consistency gate passed its current checks. |
| `php tooling/governance/check-semantic-phpdoc.php` | FAIL | 14533 scanned, 17261 violations | YES | Pre-existing repo-wide/touched-scope governance debt is currently enforced as HIGH findings. Includes active production files and `.idea/vendor` false-scope entries. |
| `php tooling/governance/check-how-to-document-structure.php` | FAIL | 19 scanned, 8 violations | YES | 3 HIGH unclosed-fence findings and 5 MEDIUM duplicate heading-number findings in `.agents/how-to/*.md`. |
| `php tooling/governance/check-serviceprovider-governance-consistency.php` | PASS | 19 scanned | NO | ServiceProvider wording is consistent. |
| `php tooling/governance/check-canonical-terms.php` | PASS | not reported | NO | Required columns and critical terms present. |
| `php tooling/governance/check-large-unit-thresholds.php` | FAIL | 14770 scanned, 367 findings | YES | 1 BLOCKER: `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` is 1731 lines and unclassified. 366 REVIEW findings. |
| `php tooling/governance/check-quality-ratchet.php` | PASS_WITH_MANUAL_REVIEW | tracked metrics | NO | Baseline exists, but semantic PHPDoc current value is `TBD` because the new gate is not reconciled. |
| `php tooling/governance/check-security-commit-block-readiness.php` | PASS | 9 checks | NO | Security commit block governance present. |
| `php tooling/governance/check-gate-self-tests.php` | PASS | 10/10 gates | NO | All mandatory gates have self-tests and non-zero scans. |

## 2. Baseline Classification

The repo is not currently green under the requested V5.9 gate set.

The failures are not caused by new code in this pass because no production code has been modified yet. They are current repository truth and must be reconciled before any phase can be claimed green.

## 3. Blocking Findings

| Finding | Severity | Evidence | V5.9 Blocking Decision |
|---|---|---|---|
| Semantic PHPDoc gate fails with 17,261 HIGH violations | HIGH | `EVIDENCE/v5.9-codex/raw/08-governance-gates-before.txt` | Blocks FULL_GREEN and blocks commit unless scoped/ratcheted or remediated according to governance. |
| How-to document structure gate fails with 8 findings | HIGH/MEDIUM | `EVIDENCE/v5.9-codex/raw/08-governance-gates-before.txt` | Blocks FULL_GREEN because governance docs are part of Harness-Full mode. |
| Large unit threshold gate fails due unclassified `AuthBuilder.php` | BLOCKER | `EVIDENCE/v5.9-codex/raw/08-governance-gates-before.txt` | Blocks FULL_GREEN and Phase 1 commit until classified or fixed. |

## 4. Non-Blocking Baseline Notes

- `composer dump-autoload -o` still reports a pre-existing `xhp_` PSR-4 skip in `framework/System/Foundation/compat.php`.
- PHPUnit is behavior-green but reports 1 deprecation.
- Core V5.9-relevant architecture gates pass: runtime composition, runtime assembly, PublicSurface, hollow PublicSurface.
- The V5.9 first-slice evidence remains `GREEN_WITH_ACCEPTED_YELLOW_DEBT` because root container ownership is not complete.

## 5. Decision

Status: RED_VALIDATION_OR_TRUTH_BROKEN.

Do not proceed as if baseline is GREEN.

Next allowed action: truth reconciliation for the current baseline, followed by a decision whether to fix governance gates now or formally accept exact YELLOW/RED debt with owner, target, risk, expiry, evidence, and V5.9 blocking decision.
