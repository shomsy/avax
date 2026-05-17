# V5.9 Worktree Safety Classification

Date: 2026-05-16
Stage: V5.9 Governance Baseline Classification, Gate Ratchet Correction & Next Blocker Selection
Branch: `main`
Commit: `4698be5f1383974bc82531c8a3af3cf8a68df41b`

## 1. Worktree Commands

| Command                         | Result                                                                                                                                                                                                                                                              |
|---------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `git status --short`            | Dirty worktree; no staged files.                                                                                                                                                                                                                                    |
| `git diff --stat`               | 11 tracked files changed; `EVIDENCE/v5.9-codex/` untracked.                                                                                                                                                                                                         |
| `git diff --name-only`          | `.agents/how-to/how-to.txt`, `.agents/management/ACTIVE.md`, `.agents/management/BUGS.md`, `.agents/management/TODO.md`, `.codex`, `CURRENT_TRUTH.md`, `EVIDENCE/EXECUTION.md`, `avax.part-1-of-4.txt`, `avax.part-3-of-4.txt`, `avax.part-4-of-4.txt`, `avax.txt`. |
| `git diff --cached --stat`      | empty                                                                                                                                                                                                                                                               |
| `git diff --cached --name-only` | empty                                                                                                                                                                                                                                                               |
| `git branch --show-current`     | `main`                                                                                                                                                                                                                                                              |
| `git log -20 --oneline`         | latest commit `4698be5f1 fix-this fixes`; latest Boot DSL correction `32864e068`.                                                                                                                                                                                   |

## 2. File Classification

| File/path                                                | Status                                          |   Intentional? |         Stage? | Reason                                                                                        |
|----------------------------------------------------------|-------------------------------------------------|---------------:|---------------:|-----------------------------------------------------------------------------------------------|
| `CURRENT_TRUTH.md`                                       | modified                                        |            YES |            YES | Prior V5.9 Codex truth reconciliation plus this phase truth update.                           |
| `EVIDENCE/EXECUTION.md`                                  | modified                                        |            YES |            YES | Prior V5.9 Codex execution update plus this phase execution update.                           |
| `.agents/management/TODO.md`                             | modified                                        |            YES |            YES | Prior V5.9 Codex backlog item plus this phase status update.                                  |
| `.agents/management/ACTIVE.md`                           | modified                                        |            YES |            YES | Prior V5.9 Codex active board update plus this phase status update.                           |
| `.agents/management/BUGS.md`                             | modified                                        |            YES |            YES | Prior V5.9 Codex bug item plus this phase status update.                                      |
| `EVIDENCE/v5.9-codex/**`                                 | untracked/modified                              |            YES |            YES | Required evidence and raw validation output for V5.9 Codex execution.                         |
| `tooling/governance/check-semantic-phpdoc.php`           | clean before implementation                     | YES if changed | YES if changed | In scope for semantic PHPDoc ratchet correction.                                              |
| `tooling/governance/check-how-to-document-structure.php` | clean before implementation                     | YES if changed | YES if changed | In scope only if a narrow false-positive fix is required.                                     |
| `tooling/governance/check-large-unit-thresholds.php`     | clean before implementation                     | YES if changed | YES if changed | In scope only if classification support is required.                                          |
| `tooling/governance/fixtures/**`                         | clean before implementation                     | YES if changed | YES if changed | In scope for gate fixture proof only.                                                         |
| `.agents/how-to/how-to-*.md`                             | clean before implementation except `how-to.txt` | YES if changed | YES if changed | In scope for real how-to structure findings.                                                  |
| `.agents/how-to/how-to.txt`                              | modified                                        |             NO |             NO | Pre-existing unrelated governance draft/local file; not a required `how-to-*.md` gate target. |
| `.codex`                                                 | modified                                        |             NO |             NO | User-local/tool-local file; forbidden from staging.                                           |
| `avax.txt`                                               | deleted                                         |             NO |             NO | Pre-existing user-local deletion; forbidden from staging.                                     |
| `avax.part-1-of-4.txt`                                   | modified                                        |             NO |             NO | Pre-existing local/generated dump change; forbidden from staging.                             |
| `avax.part-3-of-4.txt`                                   | modified                                        |             NO |             NO | Pre-existing local/generated dump change; forbidden from staging.                             |
| `avax.part-4-of-4.txt`                                   | modified                                        |             NO |             NO | Pre-existing local/generated dump change; forbidden from staging.                             |

## 3. Staging Rule For This Phase

Stage only:

- `CURRENT_TRUTH.md`
- `EVIDENCE/EXECUTION.md`
- `.agents/management/TODO.md`
- `.agents/management/ACTIVE.md`
- `.agents/management/BUGS.md`
- `EVIDENCE/v5.9-codex/**`
- changed governance tooling and fixtures that are directly required by this phase
- changed `.agents/how-to/how-to-*.md` files that are directly required by the how-to structure gate

Do not stage:

- `.codex`
- `avax.txt`
- `avax.part-*`
- `.agents/how-to/how-to.txt`
- generated cache files such as `.phpunit.cache`

## 4. Decision

Worktree is safe to continue if staging remains explicit by path and no user-local/generated files are added.
