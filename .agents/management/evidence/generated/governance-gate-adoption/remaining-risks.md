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
