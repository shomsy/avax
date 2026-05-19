# 16 — Raw File Gate Closure

**Date:** 2026-05-12
**Branch:** main
**Commit:** 36949b7ac25a67412251cac172d1fb36d29b86c6
**Scope:** Restore/find raw file operations gate

## Investigation

The gate existed at `tooling/security/check-raw-file-operations.php` but was referenced in canonical validation sets as `tooling/refactor/check-raw-file-operations.php`.

## Resolution

- **Actual location:** `tooling/security/check-raw-file-operations.php`
- **Canonical reference was:** `tooling/refactor/check-raw-file-operations.php`
- **Action:** Created symlink `tooling/refactor/check-raw-file-operations.php` → `../security/check-raw-file-operations.php`

## Gate Result

```
RAW FILE OPERATIONS GOVERNANCE GATE
Total: 260 | MUST FIX: 0
ALLOWED: 244
NEEDS DESIGN DECISION: 16 (compilation-related raw file ops — ALLOWED_COMPILE_PATH)

Result: PASS — 0 MUST FIX
```

The 16 NEEDS DESIGN DECISION items are all in compilation capabilities (WriteCompiledFailurePolicies, CompileFailurePolicies, CompileClassAttributes, CompileDataShapeSchema). These are compilation-time file operations, not runtime hot-path violations. Classification: ALLOWED_COMPILE_PATH.

## Status

Gate EXISTS, PASSES, and is accessible at both paths.
No mandatory gate remains UNAVAILABLE.
