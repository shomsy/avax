# EVIDENCE/v5.9/01-worktree-baseline.md

## V5.9 Boot DSL — Worktree Baseline

**Date:** 2026-05-16
**Branch:** main
**Current Commit:** ec6a0c076 (repo-truth: reconcile ApiVersioning scope and stale duplicates before V5.9)

---

## 1. Git Status

```
M .agents/how-to/how-to.txt
D avax.txt
M merge-files
?? avax.part-1-of-4.txt
?? avax.part-2-of-4.txt
?? avax.part-3-of-4.txt
?? avax.part-4-of-4.txt
```

No staged changes.

---

## 2. Classification

| File                        | Status    | Classification                | Action for V5.9                        |
|-----------------------------|-----------|-------------------------------|----------------------------------------|
| `.agents/how-to/how-to.txt` | Modified  | Intentional pre-existing work | Do not stage unless part of V5.9 scope |
| `avax.txt`                  | Deleted   | Intentional pre-existing work | Do not restore                         |
| `merge-files`               | Modified  | Pre-existing operational file | Do not stage                           |
| `avax.part-1-of-4.txt`      | Untracked | Local dump/snapshot           | Ignore                                 |
| `avax.part-2-of-4.txt`      | Untracked | Local dump/snapshot           | Ignore                                 |
| `avax.part-3-of-4.txt`      | Untracked | Local dump/snapshot           | Ignore                                 |
| `avax.part-4-of-4.txt`      | Untracked | Local dump/snapshot           | Ignore                                 |

---

## 3. Worktree Safety

| Area                  | Status                                                                           |
|-----------------------|----------------------------------------------------------------------------------|
| `.qoder/worktrees/**` | Present — 3 worktrees found. These are isolated agent contexts, NOT active HEAD. |
| `.phpunit.cache/**`   | May exist from test runs. Must not be staged.                                    |
| `EVIDENCE/**`         | Clean — no dirty evidence files from prior sessions.                             |
| `vendor/**`           | Present. Must not be staged.                                                     |

---

## 4. Rules for V5.9 Commits

- Do NOT stage `.agents/how-to/how-to.txt` unless V5.9 explicitly modifies it.
- Do NOT stage `merge-files`.
- Do NOT restore `avax.txt`.
- Do NOT stage `avax.part-*.txt` files.
- Do NOT stage `.qoder/worktrees/**`.
- Do NOT stage `.phpunit.cache/**`.
- Do NOT stage `vendor/**`.
- Do NOT mix unrelated dirty files into V5.9 commits.

---

## 5. Baseline Validation Status (before V5.9 work)

- PHPUnit: 8413 tests, 24137 assertions, 0 errors, 0 failures
- PHPStan: 0 errors
- All gates: PASS
- Recursive governance review: 0 unresolved findings

**Status: V5.9 boot DSL may begin. Worktree is clean for V5.9 scope.**
