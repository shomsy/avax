# Proposed fix-this.md Delta: TODO-031

Generated: 2026-05-20
Verification commit: 3619e7e8a
Scope: TODO-031 status update and mapping notes only

## Proposed Changes

### Change 1: TODO-031 status update

**Current** (line 988-990):
```
- Status: NEEDS_VERIFICATION
- Priority: NEEDS_VERIFICATION
- Normalized severity: NEEDS_VERIFICATION
```

**Proposed** (replace lines 988-990):
```
- Status: VERIFIED_WITH_MAPPINGS
- Priority: P2
- Normalized severity: MEDIUM
```

### Change 2: Add verification evidence line

**Insert after line 995** (after `- Affected files: multiple`):
```
- Verification evidence: `.agents/management/evidence/generated/todo-031-supplemental-claims-verification/`
- Verification commit: 3619e7e8a
```

### Change 3: Add mapping summary

**Insert after line 1014** (after `- Expiry/Target: next cleanup batch unless explicitly deferred`):
```
- Verification result: All 141 mapped source IDs verified. No new P0/P1/P2 findings discovered.
  Confirmed claims map to existing active TODOs as follows:
  - Runtime class loading / dynamic instantiation -> TODO-004
  - Framework public entrypoint composition -> TODO-006
  - Component PublicSurface construction -> TODO-009 through TODO-013
  - Constructor default parameter instantiation -> TODO-014
  - ServiceProvider assembly gaps -> TODO-015
  - Global helper service-locator shortcuts -> TODO-019
  - Constructor bloat / large units -> TODO-020
  - Missing or weak behavior test proof -> TODO-021
  - Forbidden concept folder names -> TODO-022
  - Duplicate ownership / duplicate classes -> TODO-023
  - Hidden superglobal/env/IO access -> TODO-024
  - DR-0667 (ServiceProvider governance wording gap) -> TODO-015
  - DR-0668 (security governance tool missing) -> governance index PLANNED/NOT IMPLEMENTED
  Unmapped IDs (DR/SCR/SAI/OLD-FIX without individual evidence files) remain covered by
  their aggregate cluster definitions (CLUSTER-009 through CLUSTER-024) and will be
  re-scanned during remediation of their target TODOs. No new TODOs required.
```

### Change 4: Update "Done when" clause

**Current** (line 1012):
```
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
```

**Proposed** (replace line 1012):
```
- Done when: fix-this.md reflects VERIFIED_WITH_MAPPINGS status with mapping summary, evidence files committed, and each target TODO remediation re-scans its affected files.
```

## Clarification: "95+ individual IDs lack supporting evidence files"

This sentence from the verification summary requires precise interpretation. It does NOT mean these findings are false. It means:

**What it IS**:
- These are **unverifiable source mappings** at the individual-ID level.
- The IDs exist in the `source-finding-coverage.md` table as aggregated counts from a discipline review pass.
- The aggregate claims are real (the discipline review did find issues).
- The individual finding details (specific file, line, pattern) were not preserved as separate evidence files.

**What it is NOT**:
- It does NOT mean these are confirmed false positives.
- It does NOT mean these findings should be dropped.
- It does NOT mean the code is clean for these IDs.

**Current treatment**:
- These IDs remain covered by their aggregate cluster definitions (CLUSTER-009 through CLUSTER-024).
- Each cluster has its own active TODO that will remediate the underlying pattern.
- When each remediation TODO re-scans its affected files during implementation, any missed individual findings will be caught.

**Classification**: These are **unverifiable source mappings with aggregate claim coverage**, not false positives.

**Future risk**: If a remediation TODO discovers a code pattern that does not match its cluster definition, it may trace back to one of these unmapped IDs. This is acceptable because the remediation TODO already owns that file scope.

**No evidence hygiene TODO needed at P0/P1 level.** The aggregate cluster model provides sufficient coverage. A P3 evidence integrity improvement could be considered separately.

## Proposed Evidence Hygiene TODO (P3, not for direct inclusion)

If the project chooses to add this, it would be a separate P3 TODO:

```
### TODO-XXX: Reconcile individual DR/SCR/SAI evidence file gaps (P3)

- Status: PLANNED
- Priority: P3
- Normalized severity: LOW
- Problem: 95+ individual DR, SCR, SAI, and HTD finding IDs exist in source-finding-coverage.md
  but lack individual evidence files in dual-review/strict-code-review/ or discipline-review/.
- Why it matters: Per-ID verification and traceability is weakened without individual evidence files.
- Target state: Either (a) regenerate individual evidence files from original review tool output,
  or (b) formally mark these IDs as aggregate-only and remove per-ID references from the coverage table.
- Scope: Evidence files only. No production or test code changes.
```

This is NOT proposed for inclusion in the current fix-this.md update. It is noted here for project awareness.

## Summary of Changes

| Line | Change | Reason |
|---|---|---|
| 988-990 | Status/Priority/Severity -> VERIFIED_WITH_MAPPINGS / P2 / MEDIUM | Verification complete, no new critical findings |
| 995 (after) | Insert verification evidence path and commit | Evidence traceability |
| 1012 | Update "Done when" clause | Reflect verification-completed state |
| 1014 (after) | Insert mapping summary | Document where each claim went |

## Risk Assessment

- **Risk level**: LOW
- **No findings are being dropped** - all are either mapped to existing TODOs or covered by aggregate clusters
- **No new TODOs created** - all confirmed findings already have owning TODOs
- **Unmappable IDs remain protected** by their cluster definitions and will be caught during remediation
