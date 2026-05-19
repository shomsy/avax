# Semantic PHPDoc Ratchet Correction

Date: 2026-05-16
Stage: V5.9 Governance Baseline Classification
Status: PASS_WITH_YELLOW_RATCHET

## 1. Decision

The previous Semantic PHPDoc result was a fake RED for this phase because the gate treated all untouched legacy
documentation debt as blocking HIGH. Current governance says:

- new production code must be semantic-PHPDoc clean immediately
- touched production code must be upgraded while touched
- untouched legacy debt may remain YELLOW only with ratchet, owner, target, risk, expiry, and evidence

The gate was corrected to enforce that policy.

## 2. Gate Behavior

| Capability | Supported now? | Evidence |
|---|---:|---|
| Default ratchet mode | YES | `php tooling/governance/check-semantic-phpdoc.php` exits 0 with `PASS_WITH_YELLOW_RATCHET` when only legacy debt remains. |
| Touched-file mode | YES | `--changed-files=...` marks matching findings as blocking. |
| Full fail-on-any mode | YES | `--scope=all` / `--fail-on-any` fails fixtures with missing/fake PHPDoc. |
| Baseline mode | YES | `--baseline-count=...` and `EVIDENCE/governance/semantic-phpdoc-ratchet-baseline.md`. |
| Ratchet baseline | YES | Baseline floor: 9823 findings. A higher count is a BLOCKER. |
| Severity classification | YES | Touched/new findings remain HIGH/BLOCKING; legacy findings are YELLOW_WITH_RATCHET. |
| Non-production local path excludes | YES | dot-local worktrees, IDE vendor, root vendor, tests, tooling, evidence, examples, and docs are excluded unless explicit fixture path is used. |

## 3. Required Classification

| Category | Count | Severity | Blocks V5.9? | Decision |
|---|---:|---|---:|---|
| Touched/new production files with missing/fake semantic PHPDoc | 0 | HIGH/BLOCKING when present | YES | Gate fails via changed-file list or git detection when available. |
| Legacy untouched semantic PHPDoc debt | 9823 | YELLOW_WITH_RATCHET | NO | Accepted as legacy debt; must ratchet down and cannot regress above 9823. |
| Baseline total | 9823 | YELLOW_WITH_RATCHET | NO | Recorded in `EVIDENCE/governance/semantic-phpdoc-ratchet-baseline.md`. |
| Non-production/local scanned paths from prior gate | removed from default scan | Scope bug | N/A | `.qoder/**`, `.idea/**`, `.agents/**`, `docs/**`, `EVIDENCE/**`, `tests/**`, `tooling/**`, `vendor/**`, and `examples/**` are not default production scan targets. |

## 4. Fixture Proof

| Scenario | Command | Result | Decision |
|---|---|---|---|
| Valid semantic PHPDoc | `php tooling/governance/check-semantic-phpdoc.php --path=tooling/governance/fixtures/semantic-phpdoc/good-class-docblock.php --scope=all` | PASS | Valid fixture passes. |
| Legacy baseline file missing PHPDoc | `php tooling/governance/check-semantic-phpdoc.php --path=tooling/governance/fixtures/semantic-phpdoc/missing-class-docblock.php --baseline-count=2` | PASS_WITH_YELLOW_RATCHET | Legacy fixture is visible but non-blocking when within baseline. |
| Touched file missing semantic PHPDoc | `php tooling/governance/check-semantic-phpdoc.php --path=tooling/governance/fixtures/semantic-phpdoc/missing-class-docblock.php --changed-files=tooling/governance/fixtures/semantic-phpdoc/missing-class-docblock.php` | FAIL | Touched-scope missing class and method docs block. |
| New public class without PHPDoc | same missing-class fixture with `--scope=all` | FAIL | New/public missing class docs block. |
| Decorative/fake PHPDoc | `php tooling/governance/check-semantic-phpdoc.php --path=tooling/governance/fixtures/semantic-phpdoc/fake-redundant-docblock.php --scope=all` | FAIL | Banned vague phrases block. |

## 5. Raw Evidence

`EVIDENCE/v5.9-codex/raw/semantic-phpdoc-ratchet-after.txt`:

| Metric | Value |
|---|---:|
| Scanned files | 3658 |
| Changed production files | 0 |
| Violations found | 9823 |
| Blocking touched/new violations | 0 |
| Legacy ratchet violations | 9823 |
| Baseline violation floor | 9823 |
| Exit code | 0 |

## 6. Accepted YELLOW

| Field | Value |
|---|---|
| Owner | AvaX governance owner |
| Target | Reduce opportunistically when files are touched; prioritize PublicSurface, runtime-critical, and security-sensitive files. |
| Risk | Legacy readability and review burden; not a runtime correctness issue by itself. |
| Expiry | Next touched-file pass or dedicated documentation hardening phase. |
| Evidence | `EVIDENCE/governance/semantic-phpdoc-ratchet-baseline.md`, `EVIDENCE/v5.9-codex/raw/semantic-phpdoc-ratchet-after.txt`. |
| V5.9 blocking decision | Not blocking while touched/new violations are 0 and total findings do not regress above 9823. |
