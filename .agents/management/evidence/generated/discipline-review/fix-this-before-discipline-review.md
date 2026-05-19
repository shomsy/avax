# Previous fix-this.md Reconciliation

Generated: 2026-05-19T22:16:56+02:00

## Current fix-this.md Summary

The previous file was a full-codebase how-to compliance review dated 2026-05-15. It mixed historical gate results, production-readiness framing, stale counts, and partial TODO language. It is preserved here before rewriting the root backlog.

## Existing Sections

- # AvaX Code Review: How-To Rule Compliance
- ## 1. Runtime Composition Leaks — 197 violations
- ### Framework — Flows & Capabilities
- ### Components — API Area (entire area is affected)
- ### Components — DataStack
- ### Components — Container (worst offender)
- ### Components — Cache
- ### Components — Operations
- ### Components — HTTP
- ### Components — Identity
- ### Components — Security
- ### Components — Other
- ## 2. Constructor Bloat — 139 classes with 8+ dependencies
- ### BLOCKER / WARNING (8+ dependencies)
- ### CHECK (5-7 dependencies — design smell)
- ## 3. Large Units — 1 BLOCKER + 365 REVIEW
- ### BLOCKER — Must Fix
- ### REVIEW (selected worst)
- ## 4. Direct Instantiation in Runtime — FAIL (48 findings)
- ### Framework Flows — Constructor Default Parameters
- ## 5. ServiceProvider Coverage — 1 MISSING
- ### Missing
- ### SCAFFOLD (accepted exceptions — not blocking)
- ## 6. Semantic PHPDoc Gaps — 17,245 HIGH
- ## 7. Public-Specific Violations Summary by Area
- ## 8. What's Missing for Production-Readiness

## Existing Open Items

- Runtime composition leaks from old scan.
- Direct instantiation/default dependency creation in runtime/public code.
- AuthBuilder large builder split/classification.
- ServiceProvider coverage gaps.
- Semantic PHPDoc legacy debt.

## Existing Closed / Obsolete Items

- Old runtime-composition leak count is obsolete because the current gate returned PASS.
- Old production-readiness framing is out of scope for this discipline cleanup planning pass.

## Classification Table

| Previous item | Summary | Classification | Reconciliation |
|---|---|---|---|
| OLD-001 | Runtime Composition Leaks — 197 violations | OBSOLETE | Current `php tooling/refactor/check-runtime-composition-leaks.php` returned PASS. |
| OLD-002 | Direct Instantiation in Runtime | MERGE_WITH_NEW_FINDING | Still valid but current direct-instantiation gate reports broader current failures. |
| OLD-003 | Large Units / AuthBuilder blocker | KEEP_BUT_REWRITE | Still valid; current gate reports AuthBuilder builder BLOCKER at 798 lines. |
| OLD-004 | ServiceProvider Coverage | KEEP_BUT_REWRITE | Still valid but old count stale; current gate reports 25 missing real-code ServiceProviders. |
| OLD-005 | Semantic PHPDoc gaps | KEEP_BUT_REWRITE | Still valid as accepted YELLOW ratchet: 9810 legacy violations, 0 touched/new blockers. |
| OLD-006 | Production-readiness blockers table | CONFLICTS_WITH_GOVERNANCE | Old file mixes release/prod readiness with cleanup backlog and stale gate claims. |
| OLD-007 | SCAFFOLD accepted exceptions | TOO_VAGUE | Exception language lacks owner/expiry/validation per AGENTS.md. |
| OLD-008 | General area summaries | DEFER_TO_ARCHIVE | Useful historical signal but too coarse for active backlog. |

## Items That Remain Valid

- Direct dependency fallback/default instantiation remains valid and is merged with `DR-*` findings.
- AuthBuilder large builder debt remains valid and becomes the first P0 TODO.
- ServiceProvider coverage remains valid with current component list.
- Semantic PHPDoc remains valid as accepted YELLOW ratchet debt.

## Items Superseded By This Discipline Review

All active execution detail is superseded by root `fix-this.md` regenerated from review artifacts. Historical details remain in this evidence file.
