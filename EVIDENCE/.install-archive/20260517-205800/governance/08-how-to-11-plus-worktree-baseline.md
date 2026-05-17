# Worktree Baseline: Stage how-to governance 11+ hardening

## Status

- **Branch**: main
- **Current Commit**: a97529645 (hardening: V5.8.8 PHPStan, Runtime Gate & Truth Integrity Closure)

## Classification

### Pre-existing dirty files (to be ignored or eventually committed if valid)

- `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` (Production refactor)
- `framework/System/Configuration/BuildApplication/Builders/ApplicationBuilder.php` (Production refactor)
- `framework/System/Flows/HandleIncomingHttp/DispatchConfiguredRoute.php` (Production refactor)
- `.agents/how-to/how-to-code-review.md` (Already has some hardening)
- `.agents/how-to/how-to-dependency-injection.md` (Already has some hardening)
- `.agents/how-to/how-to-production-readiness.md` (Already has some hardening)
- `.agents/how-to/how-to-runtime-composition.md` (Already has some hardening)
- `.agents/how-to/how-to.txt` (Aggregated rules)
- `.agents/management/TODO.md` (Task tracking)
- `CURRENT_TRUTH.md` (Truth tracking)

### Files this pass will touch (Targets)

- `.agents/how-to/how-to-architecture.md`
- `.agents/how-to/how-to-dependency-injection.md`
- `.agents/how-to/how-to-runtime-composition.md`
- `.agents/how-to/how-to-design-components.md`
- `.agents/how-to/how-to-code-review.md`
- `.agents/how-to/how-to-architecture-extension-with-ddd.md`
- `.agents/how-to/how-to-production-readiness.md`
- `.agents/how-to/how-to-git.md`
- `.agents/how-to/how-to-unit-test.md`
- `.agents/how-to/how-to-clean-code.md`
- `.agents/how-to/how-to-document.md`
- `.agents/how-to/how-to-system-security.md`
- `.agents/how-to/how-to-system-performance.md`

### Files this pass must not touch (Forbidden)

- All production code files (outside of metadata/docs if required, but task says governance-only).
- `framework/**/*.php` (except if metadata requires it, but task says no production code refactor).
- `components/**/*.php` (except if metadata requires it).

### Evidence/generated files

- `EVIDENCE/governance/07-how-to-11-plus-preflight.md`
- `EVIDENCE/governance/08-how-to-11-plus-worktree-baseline.md`
- Future EVIDENCE files 09-31.

### Untracked files

- `.agents/how-to/how-to-git.md` (New file from previous pass)
- Various `EVIDENCE/governance/` and `EVIDENCE/hardening/` files.
- `framework/System/Configuration/Builders/` (New directory)

## Rules

- **Do not revert user/pre-existing work.**
- **Do not mix unrelated dirty files into this pass.** (I will stage only governance changes for this pass's final
  commit if possible, or commit all if the user work is confirmed valid).
- **No cache files in commits.**
- **No hidden worktree changes.**
