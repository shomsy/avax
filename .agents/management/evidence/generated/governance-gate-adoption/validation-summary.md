# Validation Summary

Generated: 2026-05-26

## Commands

| Command | Result | Evidence |
|---|---|---|
| `git diff --check` | PASS | `validation-git-diff-check.out` |
| `composer dump-autoload -o` | PASS with PSR-4 warnings | `validation-composer-dump-autoload.out` |
| `vendor/bin/phpunit` | PASS | `validation-phpunit.out` |
| `vendor/bin/phpstan analyse --memory-limit=1G --error-format=raw --no-progress` | FAIL, expected legacy full-mode RED | `validation-phpstan-full.raw` |
| `php tooling/validation/check-phpstan-baseline.php --mode=baseline` | PASS | `validation-phpstan-baseline.out` |
| `php tooling/validation/check-phpstan-baseline.php --mode=changed` | PASS | `validation-phpstan-changed.out` |
| `php tooling/governance/check-governance-canonical-truth.php` | PASS | `validation-canonical-truth.out` |
| `php tooling/governance/check-governance-leakage.php` | PASS | `validation-leakage.out` |
| `php tooling/governance/check-governance-index-current.php` | PASS | `validation-governance-index.out` |
| `php tooling/governance/check-stage-lock.php` | PASS | `validation-stage-lock.out` |
| `php tooling/governance/check-self-explaining-architecture.php --mode=baseline` | PASS | `validation-self-baseline.out` |
| `php tooling/governance/check-self-explaining-architecture.php --mode=changed` | PASS | `validation-self-changed.out` |
| `php tooling/testing/check-shallow-tests.php --mode=baseline` | PASS | `validation-shallow-baseline.out` |
| `php tooling/testing/check-shallow-tests.php --mode=changed` | PASS | `validation-shallow-changed.out` |
| `php tooling/governance/generate-review-packs.php governance-gate-adoption-blocked` | PASS | `validation-review-pack-generation.out` |
| `unzip -t` for all six blocked review ZIPs | PASS | terminal validation output |

## Important Output

PHPUnit:

```text
OK (9551 tests, 27380 assertions)
```

PHPStan baseline:

```text
Baseline entries: 100
Current findings: 100
New findings: 0
Stale baseline entries: 0
GREEN_WITH_BASELINE
```

Self-explaining baseline:

```text
Baseline entries: 563
Current findings: 563
New findings: 0
Stale baseline entries: 0
GREEN_WITH_BASELINE
```

Shallow-test baseline:

```text
Baseline entries: 392
Current findings: 392
New findings: 0
Stale baseline entries: 0
GREEN_WITH_BASELINE
```

## Classification

This pass is not FULL_GREEN because full PHPStan still fails and full self-explaining/shallow-test modes still have legacy findings.

The correct classification is:

```text
YELLOW_BASELINED_READY_FOR_IDENTITY_SLICE
```
*** Add File: .agents/management/evidence/generated/governance-gate-adoption/remaining-risks.md
# Remaining Risks

Generated: 2026-05-26

## Accepted YELLOW Debt

| Area | Count | Owner | Mitigation |
|---|---:|---|---|
| PHPStan legacy findings | 100 | AvaX governance remediation backlog | Baseline mode blocks new/stale entries; changed mode blocks configured changed-scope findings. |
| Self-explaining architecture legacy findings | 563 | AvaX governance remediation backlog | Baseline mode blocks new/stale entries; changed mode blocks HIGH/BLOCKER findings in touched scope. |
| Shallow-test legacy findings | 392 | AvaX governance remediation backlog | Baseline mode blocks new/stale entries; changed mode blocks any shallow finding in changed tests. |

## Hard Restrictions

- Do not claim FULL_GREEN_ENTERPRISE_READY.
- Do not start broad Identity rewrite without changed-scope gates.
- Do not add new baseline entries for touched files unless a human explicitly accepts the debt.
- Do not treat baseline files as suppressions; they are debt ledgers.

## Known Limitations

- PHPStan changed mode is scoped to configured PHPStan roots: `framework/`, `components/`, and `tests/`.
- PHP inside the local Docker wrapper has no `git` binary, so changed-mode discovery uses Git index/worktree metadata fallback.
- Tooling PHP scripts were syntax-checked and functionally exercised, but full PHPStan on all tooling scripts remains noisy because existing governance scripts predate strict typed PHPStan cleanup.

## Final Risk

Status is YELLOW, not GREEN. Identity may proceed only as a bounded slice with changed-scope gates treated as hard blockers.
