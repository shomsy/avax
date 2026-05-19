# EVIDENCE/v5.9/07-correction-preflight.md

## V5.9 Boot DSL — Correction Preflight

**Date:** 2026-05-16
**Branch:** main
**Current Commit:** ec6a0c076 (base — no V5.9 code committed yet)

---

## 1. Previous GREEN Claim

The previous report claimed:
- Status: GREEN
- All validation passing
- Recursive governance review: 0 findings
- Ready for commit

---

## 2. Independent Review Findings

| # | Finding | Severity | Root Cause |
|---|---|---|---|
| 1 | Public API boundary: `Avax::dsl()` returns `BootDslBuilder` from `Configuration/BootDsl` — leaks internal namespace into public API | HIGH | PublicSurface must own public entrypoints; Configuration must assemble internally |
| 2 | Freeze is fake: `freezeContainer()` sets only `$this->frozen = true` bool flag; container remains mutable; providers can mutate after freeze | BLOCKER | SimpleContainer has no freeze/lock method; BootDslEngine tracks freeze locally only |
| 3 | Provider lifecycle broken: `registerProviders()` instantiates `new $providerClass()`, `bootProviders()` instantiates `new $providerClass()` again — different instances, state lost between register and boot | BLOCKER | Providers are not stored as instances; each phase creates new instances |
| 4 | Root container ownership overstated: Runtime/App graph dependencies are manually created in `createRuntimeAndApp()`, not resolved from container | MEDIUM | First slice uses manual graph assembly; claim "container owns graph" is false |
| 5 | `withRoutes()`/`withRouteFiles()` behavior unproven: only last route file is used, no tests, API says plural but behavior is singular | HIGH | BootDslBuilder `create()` uses `end($this->routeFiles)` — only last file |
| 6 | Tests too narrow: 6 tests prove basic lifecycle only; no provider lifecycle, freeze, route, or failure tests | MEDIUM | Tests cover enum ordering, registry ordering, builder requires path, app creates, engine advances phases |

---

## 3. Correction Scope

All 6 findings must be fixed or formally accepted as YELLOW debt.

No scope broadening:
- No runtime adapters
- No async/JIT/workers
- No AuthBuilder split
- No Response refactor
- No Fix-This cleanup

---

## 4. Final Status Rules

- FULL_GREEN requires all findings fixed and tested
- GREEN_WITH_ACCEPTED_YELLOW_DEBT requires formal acceptance with owner/target/risk/expiry/evidence
- YELLOW_WITH_EXACT_BLOCKERS if any BLOCKER is not fixed or formally accepted
- RED if validation or truth is broken

---

## 5. Worktree Status

| Check | Result |
|---|---|
| Branch | `main` |
| HEAD | `ec6a0c076` |
| Pre-existing dirty files | `.agents/how-to/how-to.txt` (M), `avax.txt` (D), `merge-files` (M) |
| V5.9 untracked | `EVIDENCE/v5.9/00-06.md`, `framework/System/Configuration/BootDsl/`, `framework/System/Flows/BootApplication/BootWithDsl.php`, `tests/Unit/Framework/Configuration/BootDsl/`, `framework/System/PublicSurface/Avax.php` (M) |

---

## 6. Correction Plan

| Finding | Decision | Action |
|---|---|---|
| 1. PublicSurface boundary | FIX | Create public wrapper in PublicSurface; delegate to internal BootDslEngine |
| 2. Fake freeze | FIX | Add real freeze to SimpleContainer; mutation methods fail after freeze |
| 3. Provider lifecycle | FIX | Instantiate providers once, store instances, reuse for register() and boot() |
| 4. Root container ownership | ACCEPT YELLOW | First slice uses manual graph assembly; document as accepted debt with target |
| 5. Route DSL behavior | FIX | Remove `withRoutes()`/`withRouteFiles()` from first slice; defer to next phase |
| 6. Test expansion | FIX | Add provider lifecycle, freeze, failure, and compatibility tests |

---

## 7. Status

**CORRECTION_PREFLIGHT COMPLETE — Ready for implementation.**
