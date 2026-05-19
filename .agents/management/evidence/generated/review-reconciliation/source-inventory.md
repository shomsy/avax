# Review Reconciliation Source Inventory

Generated: 2026-05-20T00:00:00+02:00

## Preflight Classification
- Worktree status: YELLOW; only pre-existing generated/dump/local noise was dirty before this pass.
- `.agents/AGENTS.md`: absent.
- Allowed outputs: root `fix-this.md` and `.agents/management/evidence/generated/review-reconciliation/*.md` only.

## Source Inventory
| Path | Type | Canonical status | Finding count | Severity summary | Strengths | Limitations | Can directly create TODOs |
|---|---|---|---|---|---|---|---|
| `fix-this.md` | existing backlog | LEGACY | 245 | BLOCKER=1, HIGH=116, MEDIUM=128 | Contains prior active remediation IDs and old DR mapping. | Pre-dual-review, mechanically expanded, duplicate-heavy. | NO; reconciled through clusters |
| `.agents/management/evidence/generated/discipline-review/global-governance-heatmap.md` | discipline review aggregate | CANONICAL | 668 | BLOCKER=1, HIGH=247, MEDIUM=414, LOW=5, ACCEPTED_YELLOW=1 | Canonical DR IDs and severities from discipline pass. | Aggregate rows need source context from per-unit files. | YES |
| `.agents/management/evidence/generated/discipline-review/remediation-plan.md` | discipline remediation plan | CANONICAL | 668 | BLOCKER=1, HIGH=247, MEDIUM=414, LOW=5, ACCEPTED_YELLOW=1 | Original discipline remediation grouping and validation commands. | Not fully deduplicated against dual review. | YES via DR IDs |
| `.agents/management/evidence/generated/discipline-review/components/*-review.md` | component discipline reviews | CANONICAL | 85 | - | Per-component evidence and finding tables. | Component files duplicate global DR IDs. | YES via DR IDs |
| `.agents/management/evidence/generated/discipline-review/framework/*-review.md` | framework discipline reviews | CANONICAL | 83 | - | Per-framework-unit evidence and finding tables. | Framework files duplicate global DR IDs. | YES via DR IDs |
| `.agents/management/evidence/generated/dual-review/strict-code-review/global-strict-code-review-report.md` | dual strict aggregate | CANONICAL | 665 | BLOCKER=1, HIGH=many, MEDIUM=many, LOW=some | Canonical strict review SCR structure and global decision TARGETED_REDESIGN. | Strict findings overlap DR/HTD and must be deduped. | YES via SCR IDs |
| `.agents/management/evidence/generated/dual-review/how-to-deviations/global-how-to-deviation-report.md` | dual how-to aggregate | CANONICAL | 668 | BLOCKER=1, HIGH=247, MEDIUM=414, LOW=6 | Canonical HTD structure and rule-source mapping. | Governance deviations duplicate many strict findings by design. | YES via HTD IDs |
| `.agents/management/evidence/generated/dual-review/cross-map-strict-vs-how-to.md` | cross-map | CANONICAL | 665 | - | Maps SCR to HTD and explains overlap. | Large table; not a remediation backlog. | YES as dedupe evidence |
| `strict-code-review.md` | supplemental strict review | SUPPLEMENTAL | 243 | BLOCKER=43, HIGH=119, MEDIUM=56, LOW=25 | Adds security/runtime findings not present in canonical dual artifacts; several were code-confirmed. | Untracked supplemental file; some claims are scan-only and NEEDS_VERIFICATION. | YES only after confidence classification |
| `CURRENT_TRUTH.md` | project state | SUPPLEMENTAL | 0 | - | Provides current project-state claims for contradiction awareness. | Older optimistic GREEN claims conflict with current review evidence; not used to downgrade findings. | NO |

## Validation Command Summary

| Command | Status | Evidence summary | Impact |
|---|---|---|---|
| `composer validate --no-check-publish` | PASS | `./composer.json is valid` | Composer metadata valid. |
| `composer dump-autoload -o` | PASS_WITH_WARNING | Optimized autoload generated with 9346 classes; PSR-4 warning for `framework/System/Foundation/compat.php` class `xhp_`. | Autoload generation completed; warning remains evidence, not GREEN proof. |
| `php tooling/refactor/check-component-suite-structure.php` | PASS | `PASS` | Structure gate passed. |
| `php tooling/refactor/check-duplicate-owners.php` | PASS | `PASS` | Duplicate owner scanner passed; semantic duplicate clusters remain from review evidence. |
| `php tooling/refactor/check-namespace-drift.php` | PASS | `PASS` | Namespace drift scanner passed; compiled-container source-generation risk remains separately tracked. |
| `php tooling/refactor/check-public-surface.php` | PASS | `PASS` | Scanner passed; semantic PublicSurface findings remain in TODOs. |
| `php tooling/refactor/check-runtime-composition-leaks.php` | PASS | `PASS` | Scanner passed; supplemental semantic runtime-composition risks remain in TODOs. |
| `php tooling/governance/check-governance-index-current.php` | PASS | Governance index current. | Governance index is current. |
| `php tooling/governance/check-root-evidence-hygiene.php` | PASS | Root evidence hygiene passed. | Root evidence hygiene passed. |
| `bash verify-governance.sh .` | PASS_WITH_SIDE_EFFECTS | Governance script passed and updated generated governance event/provenance/context files. | Side-effect files were not staged. |
| `php tooling/refactor/check-direct-instantiation.php` | FAIL_EXPECTED_FINDINGS | 761 output lines of constructor/default/fallback instantiation findings. | Supports TODO-004, TODO-006, TODO-009 through TODO-014. |
| `php tooling/refactor/check-constructor-bloat.php` | FAIL_EXPECTED_FINDINGS | 460 output lines of CHECK/WARNING constructor arity findings. | Supports TODO-020. |
| `php tooling/refactor/check-service-provider-coverage.php` | FAIL_EXPECTED_FINDINGS | 25 missing ServiceProvider reports. | Supports TODO-015. |
| `php tooling/refactor/check-broken-reference-semantics.php` | FAIL_EXPECTED_FINDINGS | 5 active broken references. | Supports TODO-016. |
| `php tooling/governance/check-large-unit-thresholds.php` | FAIL_EXPECTED_FINDINGS | 3887 files scanned; 107 findings; 1 BLOCKER (`AuthBuilder`), 106 REVIEW. | Supports TODO-007 and TODO-020. |

No production PHP code, tests, composer files, or tracked autoload files were intentionally changed by this planning pass. Optional gate failures are the evidence feeding the backlog, not remediation failures.

