# Governance Generalization Evidence

## Task

Decouple project-specific "AvaX" identity from generic governance rules while establishing a clear governance layering model.

## What Was Done

### Phase 1: Classification Audit

- 30 how-to files classified as GENERIC (12), MIXED (16), PROJECT_SPECIFIC (2)
- 18 skills files classified as GENERIC (8), PROJECT_SPECIFIC (10)
- Priority sorted: P1 (quick fixes), P2 (moderate refactor), P3 (heavy refactor)

### Phase 2: Generalization Refactoring

| Pass | Files | Changes | Tool |
|------|-------|---------|------|
| P1 | 9 | ~20 | tooling/governance/generalize-p1.php |
| P2 | 5 | 13 | tooling/governance/generalize-p2.php |
| P3 | 10 | 101 | tooling/governance/generalize-p3.php |

**Result:** ~260 AvaX references reduced to remaining ~35 path-only references.

**GENERIC files:** All 9 files are at 0 AvaX name references (1 exception: `avax.txt` file pattern in how-to-code-review.md, which is a legitimate gitignore reference).

### Phase 3: Structural Changes

| Change | File | Purpose |
|--------|------|---------|
| Governance Layering Model | `.agents/.../governance-layering-model.md` | Defines 4-layer model with precedence |
| Mermaid Diagrams | (embedded in layering model) | Visual governance architecture |
| Reading Order Updated | `.agents/how-to/00-reading-order.md` | Added layering model as mandatory preflight |
| Project Overlays Verified | `.agents/how-to/project/` | how-to-write-avax.md confirmed PROJECT_SPECIFIC |
| Leakage Detection Tool | `tooling/governance/check-governance-leakage.php` | Automated GENERIC file scanning |

### Phase 4: Validation

**Governance leakage scan** (9 GENERIC files):
- CRITICAL (AvaX project name): 0 real findings (1 file pattern `avax.txt` — acceptable)
- HIGH (architectural boundary references): 28 — conceptual PublicSurface/Flows/Capabilities references
- MEDIUM (tooling/evidence paths): 22 — legitimate tooling command references

## Classification Audit Updated

Governance classification audit at `.agents/.../governance-classification-audit.md` updated with before/after refactor numbers per file.

## Remaining Drift

| Severity | Count | Classification | Status |
|----------|-------|----------------|--------|
| CRITICAL | 0 | — | All resolved |
| HIGH | 28 | Conceptual path references (PublicSurface, Flows, etc.) | Accepted — these are architectural concept names used generically |
| MEDIUM | 22 | Tooling command lines and evidence paths | Accepted — legitimate in GENERIC how-to docs |

## Files Changed

33 files modified, 1 deleted, 16 added (mostly evidence/artifacts).

## Status

**GREEN_WITH_ACCEPTED_YELLOW_DEBT**

Remaining HIGH findings are conceptual architectural boundary references (PublicSurface, Flows, Capabilities) that appear in GENERIC files describing engineering practices. These are not project name leaks — they are the project's architectural pattern names used as examples in otherwise generic documentation. Full decoupling would require renaming the architectural patterns themselves, which is out of scope for this governance generalization pass.
