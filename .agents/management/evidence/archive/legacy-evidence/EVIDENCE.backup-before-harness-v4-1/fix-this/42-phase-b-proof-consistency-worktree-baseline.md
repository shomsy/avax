# Phase B Proof Consistency Worktree Baseline

**Date:** 2026-05-15

## 1. Branch

- **Branch:** main
- **Commit:** 083d4ff54 (hardening: prove phase b provider wiring and v5.9 readiness)

## 2. Git Status

```
 M .agents/how-to/how-to.txt
 M avax.txt
```

## 3. Classification

| File                        | Type                  | Intentional?                     | Action       |
|-----------------------------|-----------------------|----------------------------------|--------------|
| `.agents/how-to/how-to.txt` | Pre-existing modified | Unknown — unrelated to this pass | Do not stage |
| `avax.txt`                  | Pre-existing modified | Unknown — unrelated to this pass | Do not stage |

## 4. Untracked Files Expected from This Pass

All new files will be under `EVIDENCE/fix-this/` — these are intentional evidence files.

## 5. Rules Applied

- Do not stage `.agents/how-to/how-to.txt` or `avax.txt`
- Do not stage `.phpunit.cache/**`
- Do not stage `.qoder/worktrees/**`
- Do not stage `vendor/**`
- Only stage intentional Phase B consistency correction files
