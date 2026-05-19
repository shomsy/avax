# Worktree Baseline

Date: 2026-05-15
Branch: main
Commit: 014e97b3b

## Git Status

```
M .agents/how-to/how-to.txt
 M avax.txt
 M fix-this.md
```

## Pre-existing Dirty Files

| File                        | Type            | Action                                       |
|-----------------------------|-----------------|----------------------------------------------|
| `.agents/how-to/how-to.txt` | governance docs | DO NOT STAGE — pre-existing unrelated        |
| `avax.txt`                  | project notes   | DO NOT STAGE — pre-existing unrelated        |
| `fix-this.md`               | program source  | WILL UPDATE — this program's source document |

## Files This Program Will Touch

- Production PHP files (runtime composition, direct instantiation, AuthBuilder, ServiceProviders)
- Tests for changed production code
- Evidence files in `EVIDENCE/fix-this/`
- `CURRENT_TRUTH.md`
- `EVIDENCE/EXECUTION.md`
- `.agents/management/TODO.md`
- `.agents/management/ACTIVE.md`

## Files This Program Must NOT Touch

- `.agents/how-to/how-to.txt` — pre-existing dirty governance docs
- `avax.txt` — pre-existing dirty project notes
- `.qoder/worktrees/**` — worktree cache
- `.phpunit.cache/**` — test cache
- `vendor/` — dependencies
- Any generated/cache files

## Classification

- **Pre-existing dirty**: 3 files (all documentation, not production code)
- **Unrelated dirty**: `.agents/how-to/how-to.txt`, `avax.txt`
- **Program source**: `fix-this.md`
- **Generated/cache**: none staged
- **Worktree**: .qoder/worktrees/ exists but not dirty in main

## Rule

- Do not revert user/pre-existing work
- Do not mix unrelated dirty files into this program
- Do not stage cache/local/generated files
- No hidden worktree changes
