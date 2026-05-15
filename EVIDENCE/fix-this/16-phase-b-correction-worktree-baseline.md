# Phase B Correction Worktree Baseline

**Date:** 2026-05-15

## 1. Git State

- **Branch:** main
- **Current commit:** ae00a5a30 (Phase B Closure: Facade Self-Instantiation & Reset Proof — FULL_GREEN)
- **Ahead of origin:** 70 commits

## 2. Worktree Status

| File | Status | Classification |
|---|---|---|
| `.agents/how-to/how-to.txt` | Modified | UNRELATED — pre-existing governance text update |
| `avax.txt` | Modified | UNRELATED — LFS pointer change |
| `CURRENT_TRUTH.md` | Clean | Will be modified during correction |
| `EVIDENCE/EXECUTION.md` | Clean | Will be modified during correction |
| `components/HTTP/ApiVersioning/System/PublicSurface/ApiVersion.php` | Clean | INTENTIONAL — Phase B correction target |
| `components/Application/Pipeline/System/PublicSurface/Pipeline.php` | Clean | INTENTIONAL — Phase B correction target |
| `components/Application/Pipeline/System/PublicSurface/HookRegistry.php` | Clean | INTENTIONAL — Phase B correction target (move) |
| `tooling/refactor/check-runtime-composition-leaks.php` | Clean | INTENTIONAL — gate accuracy fix |
| `EVIDENCE/fix-this/` | Has Phase A files | INTENTIONAL — evidence directory |
| `EVIDENCE/fix-this-phase-b/` | Untracked (Phase B evidence) | INTENTIONAL |

## 3. Rules

- Do NOT stage `.agents/how-to/how-to.txt`
- Do NOT stage `avax.txt`
- Do NOT stage `.phpunit.cache/**`
- Do NOT stage `.qoder/worktrees/**`
- Only stage Phase B correction files
